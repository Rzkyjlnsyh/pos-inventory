<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\StockMovement;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesOrderController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->get('q');
        $status = $request->get('status');

        $salesOrders = SalesOrder::with(['customer', 'creator', 'approver'])
            ->when($q, fn($query) =>
                $query->where('so_number', 'like', "%$q%")
                    ->orWhereHas('customer', fn($qq) => $qq->where('name', 'like', "%$q%"))
            )
            ->when($status, fn($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(15);

        return view('owner.sales.index', compact('salesOrders', 'q', 'status'));
    }

    public function create(): View
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('owner.sales.create', compact('customers', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_date' => ['required', 'date'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'payment_method' => ['required', 'in:cash,transfer,split'],
            'payment_status' => ['required', 'in:dp,lunas'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:100'],
            'items.*.sale_price' => ['required', 'numeric', 'min:0'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $status = 'pending';

        try {
            DB::transaction(function () use ($validated, $status) {
                $soNumber = $this->generateSoNumber();

                $subtotal = collect($validated['items'])->reduce(function ($carry, $item) {
                    return $carry + ((float)$item['sale_price'] * (int)$item['qty']);
                }, 0);
                $discountTotal = collect($validated['items'])->sum(function ($item) {
                    return (float)($item['discount'] ?? 0);
                });
                $grandTotal = $subtotal - $discountTotal;

                $so = SalesOrder::create([
                    'so_number' => $soNumber,
                    'order_date' => $validated['order_date'],
                    'customer_id' => $validated['customer_id'] ?? null,
                    'subtotal' => $subtotal,
                    'discount_total' => $discountTotal,
                    'grand_total' => $grandTotal,
                    'status' => $status,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => $validated['payment_status'],
                    'created_by' => Auth::id(),
                ]);

                foreach ($validated['items'] as $item) {
                    $lineTotal = ((float)$item['sale_price'] * (int)$item['qty']) - (float)($item['discount'] ?? 0);
                    SalesOrderItem::create([
                        'sales_order_id' => $so->id,
                        'product_id' => $item['product_id'] ?? null,
                        'product_name' => $item['product_name'],
                        'sku' => $item['sku'] ?? null,
                        'sale_price' => $item['sale_price'],
                        'qty' => $item['qty'],
                        'discount' => $item['discount'] ?? 0,
                        'line_total' => $lineTotal,
                    ]);
                }
            });

            return redirect()->route('owner.sales.index')->with('success', 'Sales order dibuat dan siap untuk diproses.');
        } catch (\Exception $e) {
            \Log::error('Error storing sales order: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan SO: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(SalesOrder $salesOrder): View
    {
        $salesOrder->load(['customer', 'items', 'creator', 'approver', 'payments']);

        $payment = $salesOrder->payments->first() ?? new Payment();

        return view('owner.sales.show', compact('salesOrder', 'payment'));
    }

    public function edit(SalesOrder $salesOrder): View|RedirectResponse
    {
        if (!$salesOrder->isEditable()) {
            return back()->withErrors(['error' => 'Sales order yang selesai tidak bisa diedit.']);
        }
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('owner.sales.edit', compact('salesOrder', 'customers', 'products'));
    }

    public function update(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        if (!$salesOrder->isEditable()) {
            return back()->withErrors(['error' => 'Sales order yang selesai tidak bisa diedit.']);
        }

        $validated = $request->validate([
            'order_date' => ['required', 'date'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'payment_method' => ['required', 'in:cash,transfer,split'],
            'payment_status' => ['required', 'in:dp,lunas'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:100'],
            'items.*.sale_price' => ['required', 'numeric', 'min:0'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            DB::transaction(function () use ($salesOrder, $validated) {
                $subtotal = collect($validated['items'])->reduce(function ($carry, $item) {
                    return $carry + ((float)$item['sale_price'] * (int)$item['qty']);
                }, 0);
                $discountTotal = collect($validated['items'])->sum(function ($item) {
                    return (float)($item['discount'] ?? 0);
                });
                $grandTotal = $subtotal - $discountTotal;

                $salesOrder->update([
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
                    $lineTotal = ((float)$item['sale_price'] * (int)$item['qty']) - (float)($item['discount'] ?? 0);
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
            });

            return redirect()->route('owner.sales.show', $salesOrder)->with('success', 'Sales order diperbarui dan menunggu approval.');
        } catch (\Exception $e) {
            \Log::error('Error updating sales order: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat update SO: ' . $e->getMessage()])->withInput();
        }
    }

    public function approve(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        if ($salesOrder->status !== 'pending') {
            return back()->withErrors(['status' => 'Hanya pending yang bisa di-approve.']);
        }

        try {
            $salesOrder->update(['approved_by' => Auth::id(), 'approved_at' => Carbon::now()]);
            \Log::info('SO approved: ' . $salesOrder->so_number . ' by user ' . Auth::id());
            return back()->with('success', 'Sales order di-approve.');
        } catch (\Exception $e) {
            \Log::error('Error approving sales order: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat approve: ' . $e->getMessage()]);
        }
    }

    public function addPayment(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        \Log::info('Starting addPayment for SO: ' . $salesOrder->so_number . ', Input: ' . json_encode($request->all()));

        $validated = $request->validate([
            'payment_amount' => ['required', 'numeric', 'min:0', function ($attribute, $value, $fail) use ($salesOrder) {
                if ($salesOrder->paid_total == 0 && $value < $salesOrder->grand_total * 0.5) {
                    $fail('DP minimal 50% dari grand total: Rp ' . number_format($salesOrder->grand_total * 0.5, 0, ',', '.'));
                }
                if ($value > $salesOrder->remaining_amount) {
                    $fail('Jumlah tidak boleh melebihi sisa: Rp ' . number_format($salesOrder->remaining_amount, 0, ',', '.'));
                }
            }],
            'paid_at' => ['required', 'date'],
            'proof_path' => [
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:2048',
                function ($attribute, $value, $fail) use ($salesOrder, $request) {
                    if (in_array($salesOrder->payment_method, ['transfer', 'split']) && !$request->hasFile('proof_path')) {
                        $fail('Bukti pembayaran wajib diunggah untuk metode pembayaran ' . $salesOrder->payment_method . '.');
                    }
                },
            ],
            'reference' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            DB::transaction(function () use ($salesOrder, $validated, $request) {
                $proofPath = $request->hasFile('proof_path')
                    ? $request->file('proof_path')->store('payment-proofs', 'public')
                    : null;

                $payment = Payment::create([
                    'sales_order_id' => $salesOrder->id,
                    'method' => $salesOrder->payment_method,
                    'status' => $salesOrder->payment_status,
                    'amount' => $validated['payment_amount'],
                    'paid_at' => $validated['paid_at'],
                    'reference' => $validated['reference'] ?? null,
                    'proof_path' => $proofPath,
                    'note' => $validated['note'] ?? null,
                    'created_by' => Auth::id(),
                ]);

                $newPaidTotal = $salesOrder->paid_total + $validated['payment_amount'];
                $newPaymentStatus = $newPaidTotal >= $salesOrder->grand_total ? 'lunas' : 'dp';

                $salesOrder->update(['payment_status' => $newPaymentStatus]);

                \Log::info('Payment added for SO: ' . $salesOrder->so_number . ', Amount: ' . $validated['payment_amount'] . ', New Paid Total: ' . $newPaidTotal . ', New Payment Status: ' . $newPaymentStatus . ', Proof Path: ' . ($proofPath ?? 'None'));
            });

            return back()->with('success', 'Pembayaran ditambahkan.');
        } catch (\Exception $e) {
            \Log::error('Error adding payment for SO ' . $salesOrder->so_number . ': ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menambah pembayaran: ' . $e->getMessage()])->withInput();
        }
    }

    public function startProcess(SalesOrder $salesOrder): RedirectResponse
    {
        \Log::info('Starting startProcess for SO: ' . $salesOrder->so_number . ', Status: ' . $salesOrder->status . ', Approved: ' . ($salesOrder->approved_by ? 'Yes' : 'No') . ', Paid Total: ' . $salesOrder->paid_total);

        if ($salesOrder->status !== 'pending' || $salesOrder->approved_by === null) {
            \Log::warning('startProcess failed: Invalid status or not approved for SO ' . $salesOrder->so_number);
            return back()->withErrors(['status' => 'Hanya pending yang sudah di-approve bisa dimulai prosesnya.']);
        }

        if ($salesOrder->paid_total < $salesOrder->grand_total * 0.5) {
            \Log::warning('startProcess failed: Insufficient payment for SO ' . $salesOrder->so_number . ', Paid: ' . $salesOrder->paid_total . ', Required: ' . ($salesOrder->grand_total * 0.5));
            return back()->withErrors(['payment' => 'Pembayaran minimal 50% untuk mulai proses.']);
        }

        if (in_array($salesOrder->payment_method, ['transfer', 'split'])) {
            $paymentsWithoutProof = $salesOrder->payments()->whereNull('proof_path')->count();
            if ($paymentsWithoutProof > 0) {
                \Log::warning('startProcess failed: Missing proof of payment for SO ' . $salesOrder->so_number);
                return back()->withErrors(['payment' => 'Semua pembayaran untuk metode transfer atau split harus memiliki bukti pembayaran.']);
            }
        }

        try {
            DB::transaction(function () use ($salesOrder) {
                $this->updateStockOnPayment($salesOrder);
                $salesOrder->update(['status' => 'di proses']);
                \Log::info('SO ' . $salesOrder->so_number . ' status changed to di proses');
            });
            return back()->with('success', 'Proses dimulai.');
        } catch (\Exception $e) {
            \Log::error('Error starting process for SO ' . $salesOrder->so_number . ': ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat memulai proses: ' . $e->getMessage()]);
        }
    }

    public function printNota(Payment $payment): \Illuminate\Http\Response
    {
        $salesOrder = $payment->salesOrder;
        $pdf = Pdf::loadView('owner.sales.nota', compact('salesOrder', 'payment'));
        return $pdf->download('nota_' . $salesOrder->so_number . '_payment_' . $payment->id . '.pdf');
    }

    public function complete(SalesOrder $salesOrder): RedirectResponse
    {
        if ($salesOrder->status !== 'di proses') {
            return back()->withErrors(['status' => 'Hanya yang di proses yang bisa diselesaikan.']);
        }

        if ($salesOrder->remaining_amount > 0) {
            return back()->withErrors(['payment' => 'Pembayaran harus lunas untuk menyelesaikan.']);
        }

        try {
            $salesOrder->update(['status' => 'selesai', 'completed_at' => Carbon::now()]);
            \Log::info('SO ' . $salesOrder->so_number . ' completed');
            return back()->with('success', 'Sales order selesai.');
        } catch (\Exception $e) {
            \Log::error('Error completing sales order: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyelesaikan: ' . $e->getMessage()]);
        }
    }

    private function updateStockOnPayment(SalesOrder $salesOrder)
    {
        DB::transaction(function () use ($salesOrder) {
            foreach ($salesOrder->items as $item) {
                if ($item->product_id) {
                    $product = $item->product;
                    $initialStock = $product->stock_qty;
                    $newStock = $initialStock - $item->qty;
                    if ($newStock < 0) {
                        \Log::warning('Negative stock for product ' . $product->id . ' on SO ' . $salesOrder->so_number . ': New stock ' . $newStock);
                    }
                    $product->stock_qty = $newStock;
                    $product->save();

                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'OUTGOING',
                        'ref_code' => $salesOrder->so_number,
                        'initial_qty' => $initialStock,
                        'qty_in' => 0,
                        'qty_out' => $item->qty,
                        'final_qty' => $product->stock_qty,
                        'user_id' => Auth::id(),
                        'notes' => 'Pembayaran SO: ' . $salesOrder->so_number,
                        'moved_at' => Carbon::now(),
                    ]);
                }
            }
        });
    }

    private function generateSoNumber(): string
    {
        $date = Carbon::now()->format('ymd');
        $seq = DB::table('sales_orders')->whereDate('created_at', Carbon::today())->count() + 1;
        return 'SAL' . $date . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }
}