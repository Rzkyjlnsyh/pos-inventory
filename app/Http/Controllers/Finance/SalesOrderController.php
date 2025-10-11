<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesOrderLog;
use App\Models\Product;
use App\Models\Customer;
use App\Models\StockMovement;
use App\Models\Payment;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesOrderController extends Controller
{
    private function checkActiveShift(): bool|RedirectResponse
    {
        $activeShift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
        if (!$activeShift) {
            \Log::warning('No active shift for user: ' . Auth::id());
            return redirect()->route('finance.shift.dashboard')->with('error', 'Silakan mulai shift terlebih dahulu untuk melakukan aksi ini.');
        }
        return true;
    }

    private function logAction(SalesOrder $salesOrder, string $action, string $description): void
    {
        SalesOrderLog::create([
            'sales_order_id' => $salesOrder->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'created_at' => now(),
        ]);
    }

    public function index(Request $request): View
    {
        $q = $request->get('q');
        $status = $request->get('status');
        $payment_status = $request->get('payment_status');

        $salesOrders = SalesOrder::with(['customer', 'creator', 'approver'])
            ->when($q, fn($query) =>
                $query->where('so_number', 'like', "%$q%")
                    ->orWhereHas('customer', fn($qq) => $qq->where('name', 'like', "%$q%"))
            )
            ->when($status, fn($query) => $query->where('status', $status))
            ->when($payment_status && $payment_status !== 'all', fn($query) => $query->where('payment_status', $payment_status))
            ->orderByDesc('id')
            ->paginate(15);

        return view('finance.sales.index', compact('salesOrders', 'q', 'status', 'payment_status'));
    }

    public function create(): View|RedirectResponse
    {
        $activeShift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
        if (!$activeShift) {
            \Log::warning('No active shift for user: ' . Auth::id());
            return redirect()->route('finance.shift.dashboard')->with('error', 'Silakan mulai shift dan masukkan kas awal terlebih dahulu.');
        }
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('is_active', true)->where('price', '>', 0)->orderBy('name')->get();
        return view('finance.sales.create', compact('customers', 'products', 'activeShift'));
    }

    public function store(Request $request): RedirectResponse|View
    {
        \Log::info('Store request received', $request->all());

        $activeShift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
        if (!$activeShift) {
            \Log::error('No active shift found for user: ' . Auth::id());
            return back()->withErrors(['error' => 'Tidak ada shift aktif. Silakan mulai shift terlebih dahulu.'])->withInput();
        }

        $validated = $request->validate([
            'order_type' => ['required', 'in:jahit_sendiri,beli_jadi'],
            'order_date' => ['required', 'date'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'payment_method' => ['required', 'in:cash,transfer,split'],
            'payment_status' => ['required', 'in:dp,lunas'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:100'],
            'items.*.sale_price' => ['required', 'numeric', 'min:0.01'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'payment_amount' => ['nullable', 'numeric', 'min:0'],
            'cash_amount' => ['nullable', 'numeric', 'min:0'],
            'transfer_amount' => ['nullable', 'numeric', 'min:0'],
            'paid_at' => ['nullable', 'date'],
            'proof_path' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        \Log::info('Validated data', $validated);

        foreach ($request->items as $index => $item) {
            if (!empty($item['product_id'])) {
                $product = Product::find($item['product_id']);
                if (!$product || $product->price <= 0) {
                    \Log::error("Invalid product at index $index", $item);
                    return back()->withErrors(["items.$index.product_id" => 'Produk tidak valid atau harga kosong.'])->withInput();
                }
            }
        }

        $subtotal = collect($validated['items'])->reduce(function ($carry, $item) {
            return $carry + ((float)$item['sale_price'] * (int)$item['qty']);
        }, 0);
        $discountTotal = collect($validated['items'])->sum(function ($item) {
            return (float)($item['discount'] ?? 0) * (int)$item['qty'];
        });
        $grandTotal = $subtotal - $discountTotal;

        $cashAmount = $validated['payment_method'] === 'split' ? ($validated['cash_amount'] ?? 0) : ($validated['payment_method'] === 'cash' ? ($validated['payment_amount'] ?? 0) : 0);
        $transferAmount = $validated['payment_method'] === 'split' ? ($validated['transfer_amount'] ?? 0) : ($validated['payment_method'] === 'transfer' ? ($validated['payment_amount'] ?? 0) : 0);
        $paymentAmount = $cashAmount + $transferAmount;

        \Log::info('Calculated payment', ['payment_amount' => $paymentAmount, 'cash' => $cashAmount, 'transfer' => $transferAmount, 'grand_total' => $grandTotal]);

        if ($paymentAmount > 0) {
            if ($validated['payment_status'] === 'dp' && $paymentAmount < $grandTotal * 0.5) {
                \Log::error('Payment amount below 50% DP', ['payment_amount' => $paymentAmount, 'grand_total' => $grandTotal]);
                return back()->withErrors(['payment_amount' => 'DP minimal 50%: Rp ' . number_format($grandTotal * 0.5, 0, ',', '.')])->withInput();
            }
            if ($paymentAmount > $grandTotal) {
                \Log::error('Payment amount exceeds grand total', ['payment_amount' => $paymentAmount, 'grand_total' => $grandTotal]);
                return back()->withErrors(['payment_amount' => 'Jumlah melebihi grand total: Rp ' . number_format($grandTotal, 0, ',', '.')])->withInput();
            }
        } else {
            \Log::info('No payment amount, skipping payment creation');
        }

        $status = 'pending';

        try {
            $salesOrder = DB::transaction(function () use ($validated, $request, $cashAmount, $transferAmount, $paymentAmount, $grandTotal, $activeShift, $status, $subtotal, $discountTotal) {
                $customerId = $validated['customer_id'];

                $soNumber = $this->generateSoNumber();

                $salesOrder = SalesOrder::create([
                    'so_number' => $soNumber,
                    'order_type' => $validated['order_type'],
                    'order_date' => $validated['order_date'],
                    'customer_id' => $customerId ?? null,
                    'subtotal' => $subtotal,
                    'discount_total' => $discountTotal,
                    'grand_total' => $grandTotal,
                    'status' => $status,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => $validated['payment_status'],
                    'created_by' => Auth::id(),
                ]);

                foreach ($validated['items'] as $item) {
                    $lineTotal = ((float)$item['sale_price'] * (int)$item['qty']) - ((float)($item['discount'] ?? 0) * (int)$item['qty']);
                    SalesOrderItem::create([
                        'sales_order_id' => $salesOrder->id,
                        'product_id' => $item['product_id'] ?? null,
                        'product_name' => $item['product_name'],
                        'sku' => $item['sku'] ?? null,
                        'sale_price' => $item['sale_price'],
                        'qty' => $item['qty'],
                        'discount' => $item['discount'] ?? 0,
                        'line_total' => $lineTotal,
                    ]);
                }

                if ($paymentAmount > 0) {
                    $proofPath = $request->hasFile('proof_path')
                        ? $request->file('proof_path')->store('payment-proofs', 'public')
                        : null;

                    $paymentCategory = ($paymentAmount >= $grandTotal) ? 'pelunasan' : 'dp';

                    $payment = Payment::create([
                        'sales_order_id' => $salesOrder->id,
                        'method' => $validated['payment_method'],
                        'status' => $validated['payment_status'],
                        'category' => $paymentCategory,
                        'amount' => $paymentAmount,
                        'cash_amount' => $cashAmount,
                        'transfer_amount' => $transferAmount,
                        'paid_at' => $validated['paid_at'] ?? now(),
                        'proof_path' => $proofPath,
                        'created_by' => Auth::id(),
                    ]);

                    \Log::info('Payment created', ['payment_id' => $payment->id, 'amount' => $paymentAmount, 'proof_path' => $proofPath ?? 'none']);

                    if ($cashAmount > 0 && $activeShift) {
                        $activeShift->increment('cash_total', $cashAmount);
                    }

                    $this->logAction($salesOrder, 'payment_added', "Pembayaran ditambahkan: {$paymentCategory}, Jumlah: Rp " . number_format($paymentAmount, 0, ',', '.') . ", Metode: {$validated['payment_method']}" . ($proofPath ? "" : ", tanpa bukti"));
                }

                $this->logAction($salesOrder, 'created', "Sales order dibuat: {$soNumber}, Tipe: {$validated['order_type']}, Total: Rp " . number_format($grandTotal, 0, ',', '.'));

                return $salesOrder;
            });

            \Log::info('Sales order created successfully', ['so_number' => $salesOrder->so_number]);

            if ($paymentAmount > 0) {
                $salesOrder->load('payments');
                $payment = $salesOrder->payments->first();
                return view('finance.sales.nota', [
                    'salesOrder' => $salesOrder,
                    'payment' => $payment,
                    'autoPrint' => true,
                ]);
            }

            return redirect()->route('finance.sales.show', $salesOrder)->with('success', 'Sales order dibuat.');
        } catch (\Exception $e) {
            \Log::error('Error storing sales order: ' . $e->getMessage(), ['request' => $request->all()]);
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(SalesOrder $salesOrder): View
    {
        $salesOrder->load(['customer', 'items', 'creator', 'approver', 'payments.creator', 'logs.user']);
        $payment = $salesOrder->payments->first() ?? new Payment();
        $activeShift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
        return view('finance.sales.show', compact('salesOrder', 'payment', 'activeShift'));
    }

    public function edit(SalesOrder $salesOrder): View|RedirectResponse
    {
        $shiftCheck = $this->checkActiveShift();
        if ($shiftCheck !== true) {
            return $shiftCheck;
        }

        if (!$salesOrder->isEditable()) {
            \Log::warning('Attempt to edit non-editable SO: ' . $salesOrder->so_number);
            return back()->withErrors(['error' => 'Sales order yang selesai tidak bisa diedit.']);
        }
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('is_active', true)->where('price', '>', 0)->orderBy('name')->get();
        $activeShift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
        return view('finance.sales.edit', compact('salesOrder', 'customers', 'products', 'activeShift'));
    }

    public function update(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        $shiftCheck = $this->checkActiveShift();
        if ($shiftCheck !== true) {
            return $shiftCheck;
        }

        if (!$salesOrder->isEditable()) {
            \Log::warning('Attempt to update non-editable SO: ' . $salesOrder->so_number);
            return back()->withErrors(['error' => 'Sales order yang selesai tidak bisa diedit.']);
        }

        $validated = $request->validate([
            'order_type' => ['required', 'in:jahit_sendiri,beli_jadi'],
            'order_date' => ['required', 'date'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'payment_method' => ['required', 'in:cash,transfer,split'],
            'payment_status' => ['required', 'in:dp,lunas'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:100'],
            'items.*.sale_price' => ['required', 'numeric', 'min:0.01'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'payment_amount' => ['nullable', 'numeric', 'min:0'],
            'cash_amount' => ['nullable', 'numeric', 'min:0'],
            'transfer_amount' => ['nullable', 'numeric', 'min:0'],
            'paid_at' => ['nullable', 'date'],
            'proof_path' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        foreach ($request->items as $index => $item) {
            if (!empty($item['product_id'])) {
                $product = Product::find($item['product_id']);
                if (!$product || $product->price <= 0) {
                    \Log::error("Invalid product at index $index", $item);
                    return back()->withErrors(["items.$index.product_id" => 'Produk yang dipilih tidak memiliki harga valid.'])->withInput();
                }
            }
        }

        $subtotal = collect($validated['items'])->reduce(function ($carry, $item) {
            return $carry + ((float)$item['sale_price'] * (int)$item['qty']);
        }, 0);
        $discountTotal = collect($validated['items'])->sum(function ($item) {
            return (float)($item['discount'] ?? 0) * (int)$item['qty'];
        });
        $grandTotal = $subtotal - $discountTotal;

        $cashAmount = $validated['payment_method'] === 'split' ? ($validated['cash_amount'] ?? 0) : ($validated['payment_method'] === 'cash' ? ($validated['payment_amount'] ?? 0) : 0);
        $transferAmount = $validated['payment_method'] === 'split' ? ($validated['transfer_amount'] ?? 0) : ($validated['payment_method'] === 'transfer' ? ($validated['payment_amount'] ?? 0) : 0);
        $paymentAmount = $cashAmount + $transferAmount;

        \Log::info('Calculated payment in update', ['payment_amount' => $paymentAmount, 'cash' => $cashAmount, 'transfer' => $transferAmount, 'grand_total' => $grandTotal]);

        if ($paymentAmount > 0) {
            if ($validated['payment_status'] === 'dp' && $paymentAmount < $grandTotal * 0.5) {
                \Log::error('Payment amount below 50% DP', ['payment_amount' => $paymentAmount, 'grand_total' => $grandTotal]);
                return back()->withErrors(['payment_amount' => 'DP minimal 50%: Rp ' . number_format($grandTotal * 0.5, 0, ',', '.')])->withInput();
            }
            if ($paymentAmount > $grandTotal) {
                \Log::error('Payment amount exceeds grand total', ['payment_amount' => $paymentAmount, 'grand_total' => $grandTotal]);
                return back()->withErrors(['payment_amount' => 'Jumlah melebihi grand total: Rp ' . number_format($grandTotal, 0, ',', '.')])->withInput();
            }
        } else {
            \Log::info('No payment amount in update, skipping payment creation');
        }

        try {
            DB::transaction(function () use ($salesOrder, $validated, $request, $cashAmount, $transferAmount, $paymentAmount, $grandTotal, $subtotal, $discountTotal) {
                $salesOrder->update([
                    'order_type' => $validated['order_type'],
                    'order_date' => $validated['order_date'],
                    'customer_id' => $validated['customer_id'] ?? null,
                    'subtotal' => $subtotal,
                    'discount_total' => $discountTotal,
                    'grand_total' => $grandTotal,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => $validated['payment_status'],
                    'status' => 'pending',
                    'approved_by' => null,
                    'approved_at' => null,
                ]);

                $salesOrder->items()->delete();
                foreach ($validated['items'] as $item) {
                    $lineTotal = ((float)$item['sale_price'] * (int)$item['qty']) - ((float)($item['discount'] ?? 0) * (int)$item['qty']);
                    SalesOrderItem::create([
                        'sales_order_id' => $salesOrder->id,
                        'product_id' => $item['product_id'] ?? null,
                        'product_name' => $item['product_name'],
                        'sku' => $item['sku'] ?? null,
                        'sale_price' => $item['sale_price'],
                        'qty' => $item['qty'],
                        'discount' => $item['discount'] ?? 0,
                        'line_total' => $lineTotal,
                    ]);
                }

                if ($paymentAmount > 0) {
                    $proofPath = $request->hasFile('proof_path')
                        ? $request->file('proof_path')->store('payment-proofs', 'public')
                        : null;

                    $paymentCategory = ($paymentAmount >= $grandTotal) ? 'pelunasan' : 'dp';

                    $latestPayment = Payment::where('sales_order_id', $salesOrder->id)->latest('created_at')->first();

                    if ($latestPayment) {
                        $latestPayment->update([
                            'method' => $validated['payment_method'],
                            'status' => $validated['payment_status'],
                            'category' => $paymentCategory,
                            'amount' => $paymentAmount,
                            'cash_amount' => $cashAmount,
                            'transfer_amount' => $transferAmount,
                            'paid_at' => $validated['paid_at'] ?? now(),
                            'proof_path' => $proofPath ?? $latestPayment->proof_path,
                            'created_by' => Auth::id(),
                        ]);

                        \Log::info('Payment updated in update', ['payment_id' => $latestPayment->id, 'amount' => $paymentAmount, 'proof_path' => $proofPath ?? 'none']);

                        $this->logAction($salesOrder, 'payment_updated', "Pembayaran diperbarui: {$paymentCategory}, Jumlah: Rp " . number_format($paymentAmount, 0, ',', '.') . ", Metode: {$validated['payment_method']}" . ($proofPath ? "" : ", tanpa bukti"));
                    } else {
                        $payment = Payment::create([
                            'sales_order_id' => $salesOrder->id,
                            'method' => $validated['payment_method'],
                            'status' => $validated['payment_status'],
                            'category' => $paymentCategory,
                            'amount' => $paymentAmount,
                            'cash_amount' => $cashAmount,
                            'transfer_amount' => $transferAmount,
                            'paid_at' => $validated['paid_at'] ?? now(),
                            'proof_path' => $proofPath,
                            'created_by' => Auth::id(),
                        ]);

                        \Log::info('Payment created in update', ['payment_id' => $payment->id, 'amount' => $paymentAmount, 'proof_path' => $proofPath ?? 'none']);

                        $this->logAction($salesOrder, 'payment_added', "Pembayaran ditambahkan: {$paymentCategory}, Jumlah: Rp " . number_format($paymentAmount, 0, ',', '.') . ", Metode: {$validated['payment_method']}" . ($proofPath ? "" : ", tanpa bukti"));
                    }

                    $activeShift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
                    if ($activeShift && $cashAmount > 0) {
                        $activeShift->increment('cash_total', $cashAmount);
                    }
                }

                $this->logAction($salesOrder, 'updated', "Sales order diperbarui: Tipe: {$validated['order_type']}, Total: Rp " . number_format($grandTotal, 0, ',', '.'));

                $changes = [];
                if ($salesOrder->getOriginal('order_type') !== $validated['order_type']) {
                    $changes[] = "Tipe order berubah dari {$salesOrder->getOriginal('order_type')} ke {$validated['order_type']}";
                }
                if ($salesOrder->getOriginal('payment_method') !== $validated['payment_method']) {
                    $changes[] = "Metode pembayaran berubah dari {$salesOrder->getOriginal('payment_method')} ke {$validated['payment_method']}";
                }
                if ($salesOrder->getOriginal('payment_status') !== $validated['payment_status']) {
                    $changes[] = "Status pembayaran berubah dari {$salesOrder->getOriginal('payment_status')} ke {$validated['payment_status']}";
                }
                if ($salesOrder->getOriginal('grand_total') != $grandTotal) {
                    $changes[] = "Grand total berubah dari Rp " . number_format($salesOrder->getOriginal('grand_total'), 0, ',', '.') . " ke Rp " . number_format($grandTotal, 0, ',', '.');
                }
                if (!empty($changes)) {
                    $this->logAction($salesOrder, 'updated_details', implode(', ', $changes));
                }
            });

            \Log::info('Sales order updated successfully', ['so_number' => $salesOrder->so_number]);
            return redirect()->route('finance.sales.show', $salesOrder)->with('success', 'Sales order diperbarui dan menunggu approval.');
        } catch (\Exception $e) {
            \Log::error('Error updating sales order: ' . $e->getMessage(), ['so_number' => $salesOrder->so_number]);
            return back()->withErrors(['error' => 'Terjadi kesalahan saat update SO: ' . $e->getMessage()])->withInput();
        }
    }

    public function addPayment(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        $shiftCheck = $this->checkActiveShift();
        if ($shiftCheck !== true) {
            return $shiftCheck;
        }

        $validated = $request->validate([
            'payment_amount' => ['required', 'numeric', 'min:0', function ($attribute, $value, $fail) use ($salesOrder) {
                if ($salesOrder->paid_total == 0 && $value < $salesOrder->grand_total * 0.5) {
                    $fail('DP minimal 50% dari grand total: Rp ' . number_format($salesOrder->grand_total * 0.5, 0, ',', '.'));
                }
                if ($value > $salesOrder->remaining_amount) {
                    $fail('Jumlah tidak boleh melebihi sisa: Rp ' . number_format($salesOrder->remaining_amount, 0, ',', '.'));
                }
            }],
            'payment_method' => ['required', 'in:cash,transfer,split'],
            'cash_amount' => ['nullable', 'required_if:payment_method,split', 'numeric', 'min:0'],
            'transfer_amount' => ['nullable', 'required_if:payment_method,split', 'numeric', 'min:0'],
            'paid_at' => ['required', 'date'],
            'proof_path' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'reference' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validated['payment_method'] === 'split' && $validated['payment_amount'] != ($validated['cash_amount'] ?? 0) + ($validated['transfer_amount'] ?? 0)) {
            \Log::error('Invalid split payment amount', ['payment_amount' => $validated['payment_amount'], 'cash_amount' => $validated['cash_amount'], 'transfer_amount' => $validated['transfer_amount']]);
            return back()->withErrors(['payment_amount' => 'Jumlah total harus sama dengan jumlah cash + transfer.'])->withInput();
        }

        if (($validated['payment_method'] === 'transfer' || ($validated['payment_method'] === 'split' && ($validated['transfer_amount'] ?? 0) > 0)) && !$request->hasFile('proof_path')) {
            \Log::error('Missing proof of payment for transfer/split', ['payment_method' => $validated['payment_method'], 'transfer_amount' => $validated['transfer_amount']]);
            return back()->withErrors(['proof_path' => 'Bukti wajib untuk metode transfer atau split dengan jumlah transfer.'])->withInput();
        }

        try {
            DB::transaction(function () use ($salesOrder, $validated, $request) {
                $proofPath = $request->hasFile('proof_path')
                    ? $request->file('proof_path')->store('payment-proofs', 'public')
                    : null;

                $cashAmount = $validated['payment_method'] === 'cash' ? $validated['payment_amount'] : ($validated['payment_method'] === 'split' ? ($validated['cash_amount'] ?? 0) : 0);
                $transferAmount = $validated['payment_method'] == 'transfer' ? $validated['payment_amount'] : ($validated['payment_method'] === 'split' ? ($validated['transfer_amount'] ?? 0) : 0);

                $paidBefore = $salesOrder->payments()->sum('amount');
                $newPaidTotal = $paidBefore + $validated['payment_amount'];
                $paymentCategory = ($newPaidTotal >= $salesOrder->grand_total) ? 'pelunasan' : 'dp';

                $payment = Payment::create([
                    'sales_order_id' => $salesOrder->id,
                    'method' => $validated['payment_method'],
                    'status' => ($newPaidTotal >= $salesOrder->grand_total) ? 'lunas' : 'dp',
                    'category' => $paymentCategory,
                    'amount' => $validated['payment_amount'],
                    'cash_amount' => $cashAmount,
                    'transfer_amount' => $transferAmount,
                    'paid_at' => $validated['paid_at'],
                    'reference' => $validated['reference'] ?? null,
                    'proof_path' => $proofPath,
                    'note' => $validated['note'] ?? null,
                    'created_by' => Auth::id(),
                ]);

                $salesOrder->update(['payment_status' => ($newPaidTotal >= $salesOrder->grand_total) ? 'lunas' : 'dp']);

                $activeShift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
                if ($activeShift && $cashAmount > 0) {
                    $activeShift->increment('cash_total', $cashAmount);
                }

                $this->logAction($salesOrder, 'payment_added', "Pembayaran ditambahkan: {$paymentCategory}, Jumlah: Rp " . number_format($validated['payment_amount'], 0, ',', '.') . ", Metode: {$validated['payment_method']}");
            });

            \Log::info('Payment added successfully', ['so_number' => $salesOrder->so_number]);
            return back()->with('success', 'Pembayaran ditambahkan.');
        } catch (\Exception $e) {
            \Log::error('Error adding payment for SO ' . $salesOrder->so_number . ': ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menambah pembayaran: ' . $e->getMessage()])->withInput();
        }
    }

    private function generateSoNumber(): string
    {
        $date = Carbon::now()->format('ymd');
        $seq = DB::table('sales_orders')->whereDate('created_at', Carbon::today())->count() + 1;
        return 'SAL' . $date . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }
    public function printNotaDirect(Payment $payment): View
    {
        $salesOrder = $payment->salesOrder;
        return view('finance.sales.nota', [
            'salesOrder' => $salesOrder,
            'payment' => $payment,
            'autoPrint' => true,
        ]);
    }
}