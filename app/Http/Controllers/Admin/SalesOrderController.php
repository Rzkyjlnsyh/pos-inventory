<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesOrderLog;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
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
use App\Exports\SalesOrderTemplateExport;
use App\Exports\SalesOrderExport;
use App\Imports\SalesOrderImport;
use Maatwebsite\Excel\Facades\Excel;

class SalesOrderController extends Controller
{
    private function checkActiveShift(): bool|RedirectResponse
    {
        $activeShift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
        if (!$activeShift) {
            \Log::warning('No active shift for user: ' . Auth::id());
            return redirect()->route('admin.shift.dashboard')->with('error', 'Silakan mulai shift terlebih dahulu untuk melakukan aksi ini.');
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

    public function downloadTemplate()
{
    return Excel::download(new SalesOrderTemplateExport(), 'template-import-sales-order.xlsx');
}

/**
 * Export sales orders to Excel
 */
public function export(Request $request)
{
    $q = $request->get('q');
    $status = $request->get('status');
    $payment_status = $request->get('payment_status');

    $salesOrders = SalesOrder::with(['customer', 'items', 'creator'])
        ->when($q, fn($query) =>
            $query->where('so_number', 'like', "%$q%")
                ->orWhereHas('customer', fn($qq) => $qq->where('name', 'like', "%$q%"))
        )
        ->when($status, fn($query) => $query->where('status', $status))
        ->when($payment_status && $payment_status !== 'all', fn($query) => $query->where('payment_status', $payment_status))
        ->orderByDesc('id')
        ->get();

    $filename = 'sales-orders-' . date('Y-m-d-H-i') . '.xlsx';

    return Excel::download(new SalesOrderExport($salesOrders), $filename);
}

/**
 * Show import form
 */
public function importForm(): View
{
    return view('admin.sales.import');
}

/**
 * Process import sales order
 */
public function import(Request $request): RedirectResponse
{
    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls|max:2048'
    ]);

    try {
        $import = new SalesOrderImport();
        Excel::import($import, $request->file('file'));

        if (!empty($import->errors)) {
            return back()->withErrors(['import_errors' => $import->errors]);
        }

        $message = "Import berhasil! {$import->successCount} data sales order diproses.";
        if (!empty($import->errors)) {
            $message .= " Terdapat " . count($import->errors) . " error.";
        }

        return redirect()->route('admin.sales.index')->with('success', $message);

    } catch (\Exception $e) {
        return back()->withErrors(['error' => 'Terjadi kesalahan saat import: ' . $e->getMessage()]);
    }
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

        return view('admin.sales.index', compact('salesOrders', 'q', 'status', 'payment_status'));
    }

    public function create(): View|RedirectResponse
    {
        $activeShift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
        if (!$activeShift) {
            return redirect()->route('admin.shift.dashboard')->with('error', 'Silakan mulai shift dan masukkan kas awal terlebih dahulu.');
        }
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('is_active', true)->where('price', '>', 0)->orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get(); // ✅ Tambahkan ini
        return view('admin.sales.create', compact('customers', 'products', 'activeShift', 'suppliers')); // ✅ Tambahkan 'suppliers'
    }

    public function store(Request $request): RedirectResponse|View
    {
        \Log::info('Store request received', $request->all());
        $activeShift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
        if (!$activeShift) {
            \Log::error('No active shift found for user: ' . Auth::id());
            return back()->withErrors(['error' => 'Tidak ada shift aktif. Silakan mulai shift terlebih dahulu.'])->withInput();
        }
    
        // Tentukan status dari input (draft atau pending)
        $status = $request->input('status', 'pending');
    
        // Validasi dasar (selalu wajib)
        $validated = $request->validate([
            'order_type' => ['required', 'in:jahit_sendiri,beli_jadi'],
            'order_date' => ['required', 'date'],
            'deadline' => ['nullable', 'date'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string', 'max:255'], 
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'payment_method' => $status === 'draft' ? ['nullable', 'in:cash,transfer,split'] : ['required', 'in:cash,transfer,split'],
            'payment_status' => $status === 'draft' ? ['nullable', 'in:dp,lunas'] : ['required', 'in:dp,lunas'],
            'add_to_purchase' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:100'],
            'items.*.sale_price' => ['required', 'numeric', 'min:0.01'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'discount_total' => ['nullable', 'numeric', 'min:0'],
            'payment_amount' => $status === 'draft' ? ['nullable'] : ['nullable', 'numeric', 'min:0'],
            'cash_amount' => $status === 'draft' ? ['nullable'] : ['nullable', 'numeric', 'min:0'],
            'transfer_amount' => $status === 'draft' ? ['nullable'] : ['nullable', 'numeric', 'min:0'],
            'paid_at' => $status === 'draft' ? ['nullable'] : ['nullable', 'date'],
            'proof_path' => $status === 'draft' ? ['nullable'] : ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'reference_number' => $status === 'draft' ? ['nullable'] : ['nullable', 'string', 'max:100'],
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
        $discountTotal = (float)($validated['discount_total'] ?? 0);
        $grandTotal = $subtotal - $discountTotal;
    
        $cashAmount = 0;
        $transferAmount = 0;
        $paymentAmount = 0;
    
        if ($status !== 'draft') {
            $cashAmount = $validated['payment_method'] === 'split' ? ($validated['cash_amount'] ?? 0) : ($validated['payment_method'] === 'cash' ? ($validated['payment_amount'] ?? 0) : 0);
            $transferAmount = $validated['payment_method'] === 'split' ? ($validated['transfer_amount'] ?? 0) : ($validated['payment_method'] === 'transfer' ? ($validated['payment_amount'] ?? 0) : 0);
            $paymentAmount = $cashAmount + $transferAmount;
    
            \Log::info('Calculated payment', ['payment_amount' => $paymentAmount, 'cash' => $cashAmount, 'transfer' => $transferAmount, 'grand_total' => $grandTotal]);
    
            // ✅ HANYA CEK JIKA BUKAN DRAFT
            if ($paymentAmount > 0) {
                // ❌ HAPUS CEK 50% DP
                if ($paymentAmount > $grandTotal) {
                    \Log::error('Payment amount exceeds grand total', ['payment_amount' => $paymentAmount, 'grand_total' => $grandTotal]);
                    return back()->withErrors(['payment_amount' => 'Jumlah melebihi grand total: Rp ' . number_format($grandTotal, 0, ',', '.')])->withInput();
                }
            }
        }
    
        try {
            $salesOrder = DB::transaction(function () use ($validated, $request, $cashAmount, $transferAmount, $paymentAmount, $grandTotal, $activeShift, $status, $subtotal, $discountTotal) {
                // === AUTO CREATE CUSTOMER LOGIC ===
                $customerId = $validated['customer_id'] ?? null;
                if (empty($customerId) && !empty($validated['customer_name'])) {
                    $existingCustomer = Customer::where('name', $validated['customer_name'])->first();
                    if ($existingCustomer) {
                        $customerId = $existingCustomer->id;
                        \Log::info('Using existing customer', ['customer_id' => $customerId, 'name' => $existingCustomer->name]);
                    } else {
                        $customer = Customer::create([
                            'name' => $validated['customer_name'],
                            'phone' => $validated['customer_phone'] ?? null,
                            'email' => null,
                            'address' => null,
                            'notes' => 'Auto-created from sales order',
                            'is_active' => true,
                        ]);
                        $customerId = $customer->id;
                        \Log::info('Auto-created customer', ['customer_id' => $customerId, 'name' => $customer->name, 'phone' => $customer->phone]);
                    }
                }
    
                $soNumber = $this->generateSoNumber();
                $salesOrder = SalesOrder::create([
                    'so_number' => $soNumber,
                    'order_type' => $validated['order_type'],
                    'order_date' => $validated['order_date'],
                    'customer_id' => $customerId ?? null,
                    'deadline' => $validated['deadline'] ?? null,
                    'subtotal' => $subtotal,
                    'discount_total' => $discountTotal,
                    'grand_total' => $grandTotal,
                    'status' => $status, // ✅ BISA 'draft' ATAU 'pending'
                    'payment_method' => $validated['payment_method'] ?? null,
                    'payment_status' => $validated['payment_status'] ?? null,
                    'created_by' => Auth::id(),
                    'add_to_purchase' => (bool) ($request->input('add_to_purchase') ?? false),
                ]);
    
                foreach ($validated['items'] as $item) {
                    $lineTotal = (float)$item['sale_price'] * (int)$item['qty'];
                    SalesOrderItem::create([
                        'sales_order_id' => $salesOrder->id,
                        'product_id' => $item['product_id'] ?? null,
                        'product_name' => $item['product_name'],
                        'sku' => $item['sku'] ?? null,
                        'sale_price' => $item['sale_price'],
                        'qty' => $item['qty'],
                        'discount' => 0, // SET 0 karena diskon sekarang di level order
                        'line_total' => $lineTotal,
                    ]);
                }
    
                // ✅ HANYA PROSES PEMBAYARAN JIKA BUKAN DRAFT
                if ($status !== 'draft' && $paymentAmount > 0) {
                    $proofPath = $request->hasFile('proof_path')
                        ? $request->file('proof_path')->store('payment-proofs', 'public')
                        : null;
    
                        if (in_array($validated['payment_method'], ['transfer', 'split'])) {
                            $hasProof = $request->hasFile('proof_path');
                            $hasReference = !empty($validated['reference_number']);
                            
                            if (!$hasProof && !$hasReference) {
                                return back()->withErrors([
                                    'proof_path' => 'Untuk metode transfer/split, wajib upload bukti transfer atau isi no referensi.'
                                ])->withInput();
                            }
                        }
    
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
                        'reference_number' => $validated['reference_number'] ?? null,
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
    
            // === AUTO CREATE PURCHASE ORDER JIKA DICEKLIS ===
            if ($status !== 'draft' && $request->has('add_to_purchase') && $request->boolean('add_to_purchase')) {
                $itemsToPurchase = [];
                foreach ($validated['items'] as $item) {
                    if (!empty($item['product_id'])) {
                        $product = Product::find($item['product_id']);
                        if ($product && $product->stock_qty < $item['qty']) {
                            $itemsToPurchase[] = [
                                'product_id' => $item['product_id'],
                                'product_name' => $item['product_name'],
                                'sku' => $item['sku'] ?? null,
                                'cost_price' => 0,
                                'qty' => $item['qty'],
                                'discount' => 0,
                            ];
                        }
                    } else {
                        $itemsToPurchase[] = [
                            'product_id' => null,
                            'product_name' => $item['product_name'],
                            'sku' => $item['sku'] ?? null,
                            'cost_price' => 0,
                            'qty' => $item['qty'],
                            'discount' => 0,
                        ];
                    }
                }
    
                if (!empty($itemsToPurchase)) {
                    try {
                        DB::transaction(function () use ($salesOrder, $itemsToPurchase, $request) {
                            $supplierId = $request->input('supplier_id');
                            $supplierName = $request->input('supplier_name');
                            if ($supplierId) {
                                $supplier = Supplier::findOrFail($supplierId);
                            } elseif ($supplierName) {
                                $supplier = Supplier::firstOrCreate(
                                    ['name' => $supplierName],
                                    ['is_active' => true]
                                );
                            } else {
                                $supplier = Supplier::firstOrCreate(
                                    ['name' => 'Pre-order Customer'],
                                    ['is_active' => true]
                                );
                            }
    
                            $poNumber = 'PO' . now()->format('ymd') . str_pad((string) (PurchaseOrder::whereDate('created_at', now()->toDateString())->count() + 1), 4, '0', STR_PAD_LEFT);
                            $subtotalPo = collect($itemsToPurchase)->sum(fn($i) => $i['cost_price'] * $i['qty']);
                            $discountTotalPo = collect($itemsToPurchase)->sum(fn($i) => $i['discount']);
                            $grandTotalPo = $subtotalPo - $discountTotalPo;
    
                            $purchaseOrder = PurchaseOrder::create([
                                'po_number' => $poNumber,
                                'order_date' => now(),
                                'supplier_id' => $supplier->id,
                                'purchase_type' => $salesOrder->order_type === 'jahit_sendiri' ? 'kain' : 'produk_jadi',
                                'deadline' => $salesOrder->deadline,
                                'subtotal' => $subtotalPo,
                                'discount_total' => $discountTotalPo,
                                'grand_total' => $grandTotalPo,
                                'status' => PurchaseOrder::STATUS_DRAFT,
                                'is_paid' => false,
                                'created_by' => Auth::id(),
                                'sales_order_id' => $salesOrder->id,
                            ]);
    
                            foreach ($itemsToPurchase as $item) {
                                PurchaseOrderItem::create([
                                    'purchase_order_id' => $purchaseOrder->id,
                                    'product_id' => $item['product_id'],
                                    'product_name' => $item['product_name'],
                                    'sku' => $item['sku'],
                                    'cost_price' => $item['cost_price'],
                                    'qty' => $item['qty'],
                                    'discount' => $item['discount'],
                                    'line_total' => ($item['cost_price'] * $item['qty']) - $item['discount'],
                                ]);
                            }
    
                            \App\Models\PurchaseOrderLog::create([
                                'purchase_order_id' => $purchaseOrder->id,
                                'user_id' => Auth::id(),
                                'action' => 'created',
                                'description' => "Purchase order dibuat dari Sales Order: {$salesOrder->so_number} - Customer: " . ($salesOrder->customer->name ?? 'Unknown'),
                                'created_at' => now(),
                            ]);
    
                            $this->logAction($salesOrder, 'linked_to_purchase', "Linked to Purchase Order: {$poNumber}");
                        });
                    } catch (\Exception $e) {
                        \Log::error('Error auto-creating purchase order for SO: ' . $salesOrder->so_number . ' - ' . $e->getMessage());
                    }
                }
            }
    
            return redirect()->route('admin.sales.show', $salesOrder)->with('success', 'Sales order berhasil dibuat.');
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
        return view('admin.sales.show', compact('salesOrder', 'payment', 'activeShift'));
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
        return view('admin.sales.edit', compact('salesOrder', 'customers', 'products', 'activeShift'));
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
    
        $status = $request->input('status', $salesOrder->status);
    
        $validated = $request->validate([
            'order_type' => ['required', 'in:jahit_sendiri,beli_jadi'],
            'order_date' => ['required', 'date'],
            'deadline' => ['nullable', 'date'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'payment_method' => $status === 'draft' ? ['nullable', 'in:cash,transfer,split'] : ['required', 'in:cash,transfer,split'],
            'payment_status' => $status === 'draft' ? ['nullable', 'in:dp,lunas'] : ['required', 'in:dp,lunas'],
            'add_to_purchase' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:100'],
            'items.*.sale_price' => ['required', 'numeric', 'min:0.01'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'discount_total' => ['nullable', 'numeric', 'min:0'],
            'payment_amount' => $status === 'draft' ? ['nullable'] : ['nullable', 'numeric', 'min:0'],
            'cash_amount' => $status === 'draft' ? ['nullable'] : ['nullable', 'numeric', 'min:0'],
            'transfer_amount' => $status === 'draft' ? ['nullable'] : ['nullable', 'numeric', 'min:0'],
            'paid_at' => $status === 'draft' ? ['nullable'] : ['nullable', 'date'],
            'proof_path' => $status === 'draft' ? ['nullable'] : ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'reference_number' => $status === 'draft' ? ['nullable'] : ['nullable', 'string', 'max:100'],
        ]);
    
        $items = $validated['items'] ?? [];
        if (!is_array($items)) {
            $items = [];
        }
    
        foreach ($items as $index => $item) {
            if (!empty($item['product_id'])) {
                $product = Product::find($item['product_id']);
                if (!$product || $product->price <= 0) {
                    \Log::error("Invalid product at index $index", $item);
                    return back()->withErrors(["items.$index.product_id" => 'Produk yang dipilih tidak memiliki harga valid.'])->withInput();
                }
            }
        }
    
        $subtotal = collect($items)->reduce(function ($carry, $item) {
            return $carry + ((float)$item['sale_price'] * (int)$item['qty']);
        }, 0);
        $discountTotal = collect($items)->sum(function ($item) {
            return (float)($item['discount'] ?? 0) * (int)$item['qty'];
        });
        $grandTotal = $subtotal - $discountTotal;
    
        $cashAmount = 0;
        $transferAmount = 0;
        $paymentAmount = 0;
    
        if ($status !== 'draft') {
            $cashAmount = $validated['payment_method'] === 'split' ? ($validated['cash_amount'] ?? 0) : ($validated['payment_method'] === 'cash' ? ($validated['payment_amount'] ?? 0) : 0);
            $transferAmount = $validated['payment_method'] === 'split' ? ($validated['transfer_amount'] ?? 0) : ($validated['payment_method'] === 'transfer' ? ($validated['payment_amount'] ?? 0) : 0);
            $paymentAmount = $cashAmount + $transferAmount;
    
            if ($paymentAmount > 0) {
                if ($paymentAmount > $grandTotal) {
                    \Log::error('Payment amount exceeds grand total', ['payment_amount' => $paymentAmount, 'grand_total' => $grandTotal]);
                    return back()->withErrors(['payment_amount' => 'Jumlah melebihi grand total: Rp ' . number_format($grandTotal, 0, ',', '.')])->withInput();
                }
            }
        }
    
        try {
            DB::transaction(function () use ($salesOrder, $validated, $request, $cashAmount, $transferAmount, $paymentAmount, $grandTotal, $subtotal, $discountTotal, $status, $items) {
                $customerId = $validated['customer_id'] ?? null;
    
                if (empty($customerId) && !empty($validated['customer_name'])) {
                    $existingCustomer = Customer::where('name', $validated['customer_name'])->first();
                    if ($existingCustomer) {
                        $customerId = $existingCustomer->id;
                        \Log::info('Using existing customer', ['customer_id' => $customerId, 'name' => $existingCustomer->name]);
                    } else {
                        $customer = Customer::create([
                            'name' => $validated['customer_name'],
                            'phone' => $validated['customer_phone'] ?? null,
                            'email' => null,
                            'address' => null,
                            'notes' => 'Auto-created from sales order edit',
                            'is_active' => true,
                        ]);
                        $customerId = $customer->id;
                        \Log::info('Auto-created customer in update', ['customer_id' => $customerId, 'name' => $customer->name, 'phone' => $customer->phone]);
                    }
                }
    
                $salesOrder->update([
                    'order_type' => $validated['order_type'],
                    'order_date' => $validated['order_date'],
                    'deadline' => $validated['deadline'] ?? null,
                    'customer_id' => $customerId ?? null,
                    'subtotal' => $subtotal,
                    'discount_total' => $discountTotal,
                    'grand_total' => $grandTotal,
                    'payment_method' => $validated['payment_method'] ?? null,
                    'payment_status' => $validated['payment_status'] ?? null,
                    'status' => $status,
                    'add_to_purchase' => (bool) ($request->input('add_to_purchase') ?? false),
                    'approved_by' => $status === 'draft' ? null : $salesOrder->approved_by,
                    'approved_at' => $status === 'draft' ? null : $salesOrder->approved_at,
                ]);
    
                $salesOrder->items()->delete();
                foreach ($validated['items'] as $item) {
                    $lineTotal = (float)$item['sale_price'] * (int)$item['qty'];
                    SalesOrderItem::create([
                        'sales_order_id' => $salesOrder->id,
                        'product_id' => $item['product_id'] ?? null,
                        'product_name' => $item['product_name'],
                        'sku' => $item['sku'] ?? null,
                        'sale_price' => $item['sale_price'],
                        'qty' => $item['qty'],
                        'discount' => 0, // SET 0 karena diskon sekarang di level order
                        'line_total' => $lineTotal,
                    ]);
                }
    
                if ($status !== 'draft' && $paymentAmount > 0) {
                    $proofPath = $request->hasFile('proof_path')
                        ? $request->file('proof_path')->store('payment-proofs', 'public')
                        : null;
    
                    // VALIDASI: Untuk transfer/split, wajib bukti ATAU no referensi
                    if (in_array($validated['payment_method'], ['transfer', 'split'])) {
                        $hasProof = $request->hasFile('proof_path');
                        $hasReference = !empty($validated['reference_number']);
                        
                        if (!$hasProof && !$hasReference) {
                            return back()->withErrors([
                                'proof_path' => 'Untuk metode transfer/split, wajib upload bukti transfer atau isi no referensi.'
                            ])->withInput();
                        }
                    }
    
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
                            'reference_number' => $validated['reference_number'] ?? $latestPayment->reference_number, 
                            'created_by' => Auth::id(),
                        ]);
                        $this->logAction($salesOrder, 'payment_updated', "Pembayaran diperbarui...");
                    } else {
                        Payment::create([
                            'sales_order_id' => $salesOrder->id,
                            'method' => $validated['payment_method'],
                            'status' => $validated['payment_status'],
                            'category' => $paymentCategory,
                            'amount' => $paymentAmount,
                            'cash_amount' => $cashAmount,
                            'transfer_amount' => $transferAmount,
                            'paid_at' => $validated['paid_at'] ?? now(),
                            'proof_path' => $proofPath,
                            'reference_number' => $validated['reference_number'] ?? null,
                            'created_by' => Auth::id(),
                        ]);
                        $this->logAction($salesOrder, 'payment_added', "Pembayaran ditambahkan...");
                    }
    
                    $activeShift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
                    if ($activeShift && $cashAmount > 0) {
                        $activeShift->increment('cash_total', $cashAmount);
                    }
                }
    
                // === BUAT PO HANYA SAAT DRAFT → PENDING DAN add_to_purchase = true ===
                $wasDraft = $salesOrder->getOriginal('status') === 'draft';
                if ($wasDraft && $status === 'pending' && $salesOrder->add_to_purchase) {
                    $itemsToPurchase = [];
                    foreach ($salesOrder->fresh()->items as $item) {
                        if (!empty($item->product_id)) {
                            $product = Product::find($item->product_id);
                            if ($product && $product->stock_qty < $item->qty) {
                                $itemsToPurchase[] = [
                                    'product_id' => $item->product_id,
                                    'product_name' => $item->product_name,
                                    'sku' => $item->sku ?? null,
                                    'cost_price' => 0,
                                    'qty' => $item->qty,
                                    'discount' => 0,
                                ];
                            }
                        } else {
                            $itemsToPurchase[] = [
                                'product_id' => null,
                                'product_name' => $item->product_name,
                                'sku' => $item->sku ?? null,
                                'cost_price' => 0,
                                'qty' => $item->qty,
                                'discount' => 0,
                            ];
                        }
                    }
    
                    if (!empty($itemsToPurchase)) {
                        try {
                            DB::transaction(function () use ($salesOrder, $itemsToPurchase, $request) {
                                $supplierId = $request->input('supplier_id');
                                $supplierName = $request->input('supplier_name');
                                if ($supplierId) {
                                    $supplier = Supplier::findOrFail($supplierId);
                                } elseif ($supplierName) {
                                    $supplier = Supplier::firstOrCreate(
                                        ['name' => $supplierName],
                                        ['is_active' => true]
                                    );
                                } else {
                                    $supplier = Supplier::firstOrCreate(
                                        ['name' => 'Pre-order Customer'],
                                        ['is_active' => true]
                                    );
                                }
    
                                $poNumber = 'PO' . now()->format('ymd') . str_pad((string) (PurchaseOrder::whereDate('created_at', now()->toDateString())->count() + 1), 4, '0', STR_PAD_LEFT);
                                $subtotalPo = collect($itemsToPurchase)->sum(fn($i) => $i['cost_price'] * $i['qty']);
                                $discountTotalPo = collect($itemsToPurchase)->sum(fn($i) => $i['discount']);
                                $grandTotalPo = $subtotalPo - $discountTotalPo;
    
                                $purchaseOrder = PurchaseOrder::create([
                                    'po_number' => $poNumber,
                                    'order_date' => now(),
                                    'supplier_id' => $supplier->id,
                                    'purchase_type' => $salesOrder->order_type === 'jahit_sendiri' ? 'kain' : 'produk_jadi',
                                    'deadline' => $salesOrder->deadline,
                                    'subtotal' => $subtotalPo,
                                    'discount_total' => $discountTotalPo,
                                    'grand_total' => $grandTotalPo,
                                    'status' => PurchaseOrder::STATUS_DRAFT,
                                    'is_paid' => false,
                                    'created_by' => Auth::id(),
                                ]);
    
                                foreach ($itemsToPurchase as $item) {
                                    PurchaseOrderItem::create([
                                        'purchase_order_id' => $purchaseOrder->id,
                                        'product_id' => $item['product_id'],
                                        'product_name' => $item['product_name'],
                                        'sku' => $item['sku'],
                                        'cost_price' => $item['cost_price'],
                                        'qty' => $item['qty'],
                                        'discount' => $item['discount'],
                                        'line_total' => ($item['cost_price'] * $item['qty']) - $item['discount'],
                                    ]);
                                }
    
                                \App\Models\PurchaseOrderLog::create([
                                    'purchase_order_id' => $purchaseOrder->id,
                                    'user_id' => Auth::id(),
                                    'action' => 'created',
                                    'description' => "Purchase order Dari Penjualan : {$salesOrder->so_number}",
                                    'created_at' => now(),
                                ]);
    
                                $this->logAction($salesOrder, 'linked_to_purchase', "Linked to Purchase Order: {$poNumber}");
                            });
                        } catch (\Exception $e) {
                            \Log::error('Error auto-creating PO on draft process: ' . $e->getMessage());
                        }
                    }
                }
    
                $this->logAction($salesOrder, 'updated', "Sales order diperbarui...");
            });
    
            return redirect()->route('admin.sales.show', $salesOrder)->with('success', 'Sales order berhasil diperbarui.');
        } catch (\Exception $e) {
            \Log::error('Error updating sales order: ' . $e->getMessage(), ['so_number' => $salesOrder->so_number]);
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    public function addPayment(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        $shiftCheck = $this->checkActiveShift();
        if ($shiftCheck !== true) {
            return $shiftCheck;
        }
    
        $validated = $request->validate([
            'payment_amount' => ['required', 'numeric', 'min:1', function ($attribute, $value, $fail) use ($salesOrder) {
                // Hapus syarat minimal 50%
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
            'reference_number' => ['nullable', 'string', 'max:100'],
        ]);
    
        if ($validated['payment_method'] === 'split' && $validated['payment_amount'] != ($validated['cash_amount'] ?? 0) + ($validated['transfer_amount'] ?? 0)) {
            \Log::error('Invalid split payment amount', ['payment_amount' => $validated['payment_amount'], 'cash_amount' => $validated['cash_amount'], 'transfer_amount' => $validated['transfer_amount']]);
            return back()->withErrors(['payment_amount' => 'Jumlah total harus sama dengan jumlah cash + transfer.'])->withInput();
        }
    
        // ✅ VALIDASI BARU: Untuk transfer/split, wajib bukti ATAU no referensi
        if ($validated['payment_method'] === 'transfer' || ($validated['payment_method'] === 'split' && ($validated['transfer_amount'] ?? 0) > 0)) {
            $hasProof = $request->hasFile('proof_path');
            $hasReference = !empty($validated['reference_number']);
            
            if (!$hasProof && !$hasReference) {
                \Log::error('Missing proof or reference for transfer/split', [
                    'payment_method' => $validated['payment_method'], 
                    'has_proof' => $hasProof,
                    'has_reference' => $hasReference
                ]);
                return back()->withErrors([
                    'proof_path' => 'Untuk metode transfer/split, wajib upload bukti transfer ATAU isi no referensi.'
                ])->withInput();
            }
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
                    'reference_number' => $validated['reference_number'] ?? null, // ✅ TAMBAH INI
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
        return view('admin.sales.nota', [
            'salesOrder' => $salesOrder,
            'payment' => $payment,
            'autoPrint' => true,
        ]);
    }
    public function printNota(Payment $payment): \Illuminate\Http\Response
    {
        $salesOrder = $payment->salesOrder;
        $pdf = Pdf::loadView('admin.sales.nota', compact('salesOrder', 'payment'));
        $pdf->setPaper([0, 0, 164, 1000], 'portrait'); // 58mm thermal
        return $pdf->download('nota_' . $salesOrder->so_number . '_bayar_' . $payment->id . '.pdf');
    }

    public function uploadProof(Request $request, SalesOrder $salesOrder, Payment $payment): RedirectResponse
    {

        if ($payment->sales_order_id !== $salesOrder->id) {
            \Log::warning('Invalid payment for SO: ' . $salesOrder->so_number, ['payment_id' => $payment->id]);
            return back()->withErrors(['error' => 'Pembayaran tidak valid untuk sales order ini.']);
        }

        if (!in_array($payment->method, ['transfer', 'split'])) {
            \Log::warning('Invalid payment method for proof upload: ' . $payment->method, ['so_number' => $salesOrder->so_number]);
            return back()->withErrors(['error' => 'Upload bukti hanya untuk metode transfer atau split.']);
        }

        $validated = $request->validate([
            'proof_path' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        try {
            DB::transaction(function () use ($payment, $request) {
                if ($payment->proof_path) {
                    Storage::disk('public')->delete($payment->proof_path);
                }
                $proofPath = $request->file('proof_path')->store('payment-proofs', 'public');
                $payment->update(['proof_path' => $proofPath]);
                $this->logAction($payment->salesOrder, 'proof_uploaded', "Bukti pembayaran diunggah untuk pembayaran ID {$payment->id}");
            });

            \Log::info('Proof uploaded successfully for SO: ' . $salesOrder->so_number, ['payment_id' => $payment->id]);
            return back()->with('success', 'Bukti pembayaran berhasil diunggah.');
        } catch (\Exception $e) {
            \Log::error('Error uploading proof for SO ' . $salesOrder->so_number . ': ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat mengunggah bukti: ' . $e->getMessage()]);
        }
    }
    // Tambahkan method ini di class SalesOrderController
public function searchCustomers(Request $request)
{
    $query = $request->get('q');
    
    if (strlen($query) < 2) {
        return response()->json([]);
    }
    
    $customers = Customer::where('name', 'like', "%{$query}%")
        ->orWhere('phone', 'like', "%{$query}%")
        ->where('is_active', true)
        ->limit(10)
        ->get(['id', 'name', 'phone']);
    
    return response()->json($customers);
}
}