<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\StockMovement;
use App\Models\Payment;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesOrderController extends Controller
{
    // Helper untuk check shift aktif
    private function checkActiveShift(): bool|RedirectResponse
    {
        $activeShift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
        if (!$activeShift) {
            return redirect()->route('owner.shift.dashboard')->with('error', 'Silakan mulai shift terlebih dahulu untuk melakukan aksi ini.');
        }
        return true;
    }

    public function index(Request $request): View
    {
        // Ambil parameter filter
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

        return view('owner.sales.index', compact('salesOrders', 'q', 'status', 'payment_status'));
    }

    public function create(): View|RedirectResponse
    {
        // Sudah ada check
        $activeShift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
        if (!$activeShift) {
            return redirect()->route('owner.shift.dashboard')->with('error', 'Silakan mulai shift dan masukkan kas awal terlebih dahulu.');
        }
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('is_active', true)->where('price', '>', 0)->orderBy('name')->get();
        return view('owner.sales.create', compact('customers', 'products'));
    }

    public function store(Request $request): RedirectResponse|\Illuminate\Http\Response|\Illuminate\Contracts\View\View
    {
        // Sudah ada check shift
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
            'payment_status' => ['required', 'in:dp,lunas'], // Tambah validasi payment_status
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
    
        // Validasi harga produk
        foreach ($request->items as $index => $item) {
            if (!empty($item['product_id'])) {
                $product = Product::find($item['product_id']);
                if (!$product || $product->price <= 0) {
                    return back()->withErrors(["items.$index.product_id" => 'Produk tidak valid.'])->withInput();
                }
            }
        }
    
        // Hitung subtotal, discount total, dan grand total sebelum transaksi
        $subtotal = collect($validated['items'])->reduce(function ($carry, $item) {
            return $carry + ((float)$item['sale_price'] * (int)$item['qty']);
        }, 0);
        $discountTotal = collect($validated['items'])->sum(function ($item) {
            return (float)($item['discount'] ?? 0) * (int)$item['qty']; // Diskon dikalikan qty
        });
        $grandTotal = $subtotal - $discountTotal;
    
        $cashAmount = $validated['payment_method'] === 'split' ? ($validated['cash_amount'] ?? 0) : ($validated['payment_method'] === 'cash' ? ($validated['payment_amount'] ?? 0) : 0);
        $transferAmount = $validated['payment_method'] === 'split' ? ($validated['transfer_amount'] ?? 0) : ($validated['payment_method'] === 'transfer' ? ($validated['payment_amount'] ?? 0) : 0);
        $paymentAmount = $cashAmount + $transferAmount;
    
        // MODIFIKASI: HAPUS deteksi payment_status otomatis di sini. HORMATI INPUT USER.
        // $paymentStatus = $paymentAmount >= $grandTotal ? 'lunas' : 'dp';
        // if ($validated['payment_status'] !== $paymentStatus) {
        //     \Log::warning('User input payment_status (' . $validated['payment_status'] . ') tidak sesuai dengan perhitungan (' . $paymentStatus . '). Menggunakan perhitungan.');
        //     $validated['payment_status'] = $paymentStatus;
        // }
    
        if ($paymentAmount > 0) {
            if ($validated['payment_status'] === 'dp' && $paymentAmount < $grandTotal * 0.5) {
                return back()->withErrors(['payment_amount' => 'DP minimal 50%: Rp ' . number_format($grandTotal * 0.5, 0, ',', '.')])->withInput();
            }
            if ($paymentAmount > $grandTotal) {
                return back()->withErrors(['payment_amount' => 'Jumlah melebihi grand total: Rp ' . number_format($grandTotal, 0, ',', '.')])->withInput();
            }
            if (($validated['payment_method'] === 'transfer' || $transferAmount > 0) && !$request->hasFile('proof_path')) {
                return back()->withErrors(['proof_path' => 'Bukti wajib untuk transfer.'])->withInput();
            }
        }
    
        $status = 'pending';
    
        try {
            $salesOrder = DB::transaction(function () use ($validated, $request, $cashAmount, $transferAmount, $paymentAmount, $grandTotal, $activeShift, $status, $subtotal, $discountTotal) {
                // MODIFIKASI: Hapus variabel $paymentStatus yang sudah tidak digunakan
                $soNumber = $this->generateSoNumber();
        
                $salesOrder = SalesOrder::create([
                    'so_number' => $soNumber,
                    'order_type' => $validated['order_type'],
                    'order_date' => $validated['order_date'],
                    'customer_id' => $validated['customer_id'] ?? null,
                    'subtotal' => $subtotal,
                    'discount_total' => $discountTotal,
                    'grand_total' => $grandTotal,
                    'status' => $status,
                    'payment_method' => $validated['payment_method'],
                    // MODIFIKASI: Langsung pakai payment_status dari input user
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
        
                    Payment::create([
                        'sales_order_id' => $salesOrder->id,
                        'method' => $validated['payment_method'],
                        // MODIFIKASI: Status pembayaran individual juga pakai input user
                        'status' => $validated['payment_status'],
                        'amount' => $paymentAmount,
                        'cash_amount' => $cashAmount,
                        'transfer_amount' => $transferAmount,
                        'paid_at' => $validated['paid_at'] ?? now(),
                        'proof_path' => $proofPath,
                        'created_by' => Auth::id(),
                    ]);
        
                    if ($cashAmount > 0 && $activeShift) {
                        \Log::info('Before increment - Shift cash_total: ' . $activeShift->cash_total);
                        \Log::info('Adding cash to shift: ' . $cashAmount . ', Shift ID: ' . $activeShift->id);
                        $activeShift->increment('cash_total', $cashAmount);
                        \Log::info('After increment - Shift cash_total: ' . $activeShift->cash_total);
                    }
        
                    // MODIFIKASI: HAPUS update payment_status sales order di sini.
                    // Biarkan sales order payment_status sesuai input user ('dp')
                    // $newPaymentStatus = $paymentAmount >= $grandTotal ? 'lunas' : 'dp';
                    // $salesOrder->update(['payment_status' => $newPaymentStatus]);
                }
        
                return $salesOrder;
            });
        
            \Log::info('Sales order berhasil disimpan: ' . $salesOrder->so_number);
        
            // Jika ada pembayaran, render nota untuk print otomatis
            if ($paymentAmount > 0) {
                $salesOrder->load('payments'); // Load relasi payments
                $payment = $salesOrder->payments->first(); // Ambil pembayaran pertama
                return view('owner.sales.nota', [
                    'salesOrder' => $salesOrder,
                    'payment' => $payment,
                    'autoPrint' => true, // Flag untuk trigger print di view
                ]);
            }
        
            // Jika tidak ada pembayaran, redirect ke show
            return redirect()->route('owner.sales.show', $salesOrder)->with('success', 'Sales order dibuat.');
        } catch (\Exception $e) {
            \Log::error('Error storing sales order: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(SalesOrder $salesOrder): View
    {
        // No check shift, izinkan lihat-lihat
        $salesOrder->load(['customer', 'items', 'creator', 'approver', 'payments.creator']);

        $payment = $salesOrder->payments->first() ?? new Payment();

        return view('owner.sales.show', compact('salesOrder', 'payment'));
    }

    public function edit(SalesOrder $salesOrder): View|RedirectResponse
    {
        // Tambah check shift
        $shiftCheck = $this->checkActiveShift();
        if ($shiftCheck !== true) {
            return $shiftCheck;
        }

        if (!$salesOrder->isEditable()) {
            return back()->withErrors(['error' => 'Sales order yang selesai tidak bisa diedit.']);
        }
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('is_active', true)->where('price', '>', 0)->orderBy('name')->get();
        return view('owner.sales.edit', compact('salesOrder', 'customers', 'products'));
    }

    public function update(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        // Tambah check shift
        $shiftCheck = $this->checkActiveShift();
        if ($shiftCheck !== true) {
            return $shiftCheck;
        }

        if (!$salesOrder->isEditable()) {
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
        ]);

        // Validasi harga produk dari database
        foreach ($request->items as $index => $item) {
            if (!empty($item['product_id'])) {
                $product = Product::find($item['product_id']);
                if (!$product || $product->price <= 0) {
                    return back()->withErrors(["items.$index.product_id" => 'Produk yang dipilih tidak memiliki harga valid.'])->withInput();
                }
            }
        }

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
        // Tambah check shift
        $shiftCheck = $this->checkActiveShift();
        if ($shiftCheck !== true) {
            return $shiftCheck;
        }
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
        $shiftCheck = $this->checkActiveShift();
        if ($shiftCheck !== true) {
            return $shiftCheck;
        }
        
        \Log::info('Starting addPayment for SO: ' . $salesOrder->so_number . ', Input: ' . json_encode($request->all()));
        
        $validated = $request->validate([
            'payment_amount' => ['required', 'numeric', 'min:0', function ($attribute, $value, $fail) use ($salesOrder) {
                // MODIFIKASI: Validasi DP minimal 50% hanya jika belum ada pembayaran sama sekali (paid_total == 0)
                if ($salesOrder->paid_total == 0 && $value < $salesOrder->grand_total * 0.5) {
                    $fail('DP minimal 50% dari grand total: Rp ' . number_format($salesOrder->grand_total * 0.5, 0, ',', '.'));
                }
                if ($value > $salesOrder->remaining_amount) {
                    $fail('Jumlah tidak boleh melebihi sisa: Rp ' . number_format($salesOrder->remaining_amount, 0, ',', '.'));
                }
            }],
            'payment_method' => ['required', 'in:cash,transfer,split'],
            'cash_amount' => ['nullable', 'required_if:payment_method,cash,split', 'numeric', 'min:0'],
            'transfer_amount' => ['nullable', 'required_if:payment_method,transfer,split', 'numeric', 'min:0'],
            'paid_at' => ['required', 'date'],
            'proof_path' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'reference' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);
        
        // Validasi custom untuk split: total == cash + transfer
        if ($validated['payment_method'] === 'split' && $validated['payment_amount'] != ($validated['cash_amount'] ?? 0) + ($validated['transfer_amount'] ?? 0)) {
            return back()->withErrors(['payment_amount' => 'Jumlah total harus sama dengan jumlah cash + transfer.'])->withInput();
        }
        
        try {
            DB::transaction(function () use ($salesOrder, $validated, $request) {
                $proofPath = $request->hasFile('proof_path')
                    ? $request->file('proof_path')->store('payment-proofs', 'public')
                    : null;
        
                $cashAmount = $validated['cash_amount'] ?? 0;
                $transferAmount = $validated['transfer_amount'] ?? 0;
        
                $payment = Payment::create([
                    'sales_order_id' => $salesOrder->id,
                    'method' => $validated['payment_method'],
                    'amount' => $validated['payment_amount'],
                    'cash_amount' => $cashAmount,
                    'transfer_amount' => $transferAmount,
                    'paid_at' => $validated['paid_at'],
                    'reference' => $validated['reference'] ?? null,
                    'proof_path' => $proofPath,
                    'note' => $validated['note'] ?? null,
                    'created_by' => Auth::id(),
                ]);
        
                // MODIFIKASI: Hitung ULANG total pembayaran dari semua record
                $newPaidTotal = $salesOrder->payments()->sum('amount'); // Query langsung ke database
                // Tentukan status pembayaran TERBARU berdasarkan total yang dibayar
                $newPaymentStatus = $newPaidTotal >= $salesOrder->grand_total ? 'lunas' : 'dp';
                $salesOrder->update(['payment_status' => $newPaymentStatus]);
        
                // Increment cash_total shift jika ada cash
                $activeShift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
                if ($activeShift && $cashAmount > 0) {
                    \Log::info('Before increment payment - Shift cash_total: ' . $activeShift->cash_total);
                    \Log::info('Adding payment cash to shift: ' . $cashAmount . ', Shift ID: ' . $activeShift->id);
                    $activeShift->increment('cash_total', $cashAmount);
                    \Log::info('After increment payment - Shift cash_total: ' . $activeShift->cash_total);
                }
        
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

        // Tambah check shift
        $shiftCheck = $this->checkActiveShift();
        if ($shiftCheck !== true) {
            return $shiftCheck;
        }
        \Log::info('Starting startProcess for SO: ' . $salesOrder->so_number . ', Status: ' . $salesOrder->status . ', Approved: ' . ($salesOrder->approved_by ? 'Yes' : 'No') . ', Paid Total: ' . $salesOrder->paid_total);

        if ($salesOrder->status !== 'pending') {
            \Log::warning('startProcess failed: Invalid status for SO ' . $salesOrder->so_number);
            return back()->withErrors(['status' => 'Hanya pending yang bisa dimulai prosesnya.']);
        }

        if ($salesOrder->approved_by === null) {
            \Log::warning('startProcess failed: Not approved for SO ' . $salesOrder->so_number);
            return back()->withErrors(['status' => 'Sales order harus di-approve terlebih dahulu.']);
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
                $newStatus = $salesOrder->order_type === 'jahit_sendiri' ? 'request_kain' : 'di proses';
                $salesOrder->update(['status' => $newStatus]);
                \Log::info('SO ' . $salesOrder->so_number . ' status changed to ' . $newStatus);
            });
            return back()->with('success', 'Proses dimulai.');
        } catch (\Exception $e) {
            \Log::error('Error starting process for SO ' . $salesOrder->so_number . ': ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat memulai proses: ' . $e->getMessage()]);
        }
    }

    public function processJahit(SalesOrder $salesOrder): RedirectResponse
    {
        // Tambah check shift
        $shiftCheck = $this->checkActiveShift();
        if ($shiftCheck !== true) {
            return $shiftCheck;
        }
        if ($salesOrder->order_type !== 'jahit_sendiri' || $salesOrder->status !== 'request_kain') {
            return back()->withErrors(['status' => 'Hanya SO jahit sendiri dengan status request kain yang bisa diproses jahit.']);
        }

        try {
            $salesOrder->update(['status' => 'proses_jahit']);
            \Log::info('SO ' . $salesOrder->so_number . ' status changed to proses_jahit');
            return back()->with('success', 'Proses jahit dimulai.');
        } catch (\Exception $e) {
            \Log::error('Error processing jahit for SO ' . $salesOrder->so_number . ': ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat memulai proses jahit: ' . $e->getMessage()]);
        }
    }

    public function markAsJadi(SalesOrder $salesOrder): RedirectResponse
    {
        // Tambah check shift
        $shiftCheck = $this->checkActiveShift();
        if ($shiftCheck !== true) {
            return $shiftCheck;
        }
        if ($salesOrder->order_type !== 'jahit_sendiri' || $salesOrder->status !== 'proses_jahit') {
            return back()->withErrors(['status' => 'Hanya SO jahit sendiri dengan status proses jahit yang bisa ditandai jadi.']);
        }

        try {
            $salesOrder->update(['status' => 'jadi']);
            \Log::info('SO ' . $salesOrder->so_number . ' status changed to jadi');
            return back()->with('success', 'Produk selesai dijahit.');
        } catch (\Exception $e) {
            \Log::error('Error marking as jadi for SO ' . $salesOrder->so_number . ': ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menandai jadi: ' . $e->getMessage()]);
        }
    }

    public function markAsDiterimaToko(SalesOrder $salesOrder): RedirectResponse
    {
        // Tambah check shift
        $shiftCheck = $this->checkActiveShift();
        if ($shiftCheck !== true) {
            return $shiftCheck;
        }
        $validStatuses = $salesOrder->order_type === 'jahit_sendiri' ? ['jadi'] : ['di proses'];
        if (!in_array($salesOrder->status, $validStatuses)) {
            return back()->withErrors(['status' => 'Hanya SO dengan status ' . ($salesOrder->order_type === 'jahit_sendiri' ? 'jadi' : 'di proses') . ' yang bisa ditandai diterima toko.']);
        }

        try {
            $salesOrder->update(['status' => 'diterima_toko']);
            \Log::info('SO ' . $salesOrder->so_number . ' status changed to diterima_toko');
            return back()->with('success', 'Produk diterima di toko.');
        } catch (\Exception $e) {
            \Log::error('Error marking as diterima toko for SO ' . $salesOrder->so_number . ': ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menandai diterima toko: ' . $e->getMessage()]);
        }
    }

    public function complete(SalesOrder $salesOrder): RedirectResponse
    {
        // Tambah check shift
        $shiftCheck = $this->checkActiveShift();
        if ($shiftCheck !== true) {
            return $shiftCheck;
        }
        if ($salesOrder->status !== 'diterima_toko') {
            return back()->withErrors(['status' => 'Hanya SO yang sudah diterima toko yang bisa diselesaikan.']);
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

    public function printNota(Payment $payment): \Illuminate\Http\Response
    {
        $salesOrder = $payment->salesOrder;
        $pdf = Pdf::loadView('owner.sales.nota', compact('salesOrder', 'payment'));
        return $pdf->download('nota_' . $salesOrder->so_number . '_payment_' . $payment->id . '.pdf');
    }

    public function printNotaDirect(Payment $payment): View
    {
        $salesOrder = $payment->salesOrder;
        return view('owner.sales.nota', [
            'salesOrder' => $salesOrder,
            'payment' => $payment,
            'autoPrint' => true,
        ]);
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