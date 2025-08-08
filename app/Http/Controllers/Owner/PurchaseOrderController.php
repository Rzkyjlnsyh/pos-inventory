<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\StockIn;
use App\Models\StockInItem;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Carbon\Carbon;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->get('q');
        $status = $request->get('status');

        $purchases = PurchaseOrder::with(['supplier','creator','approver'])
            ->when($q, function ($query) use ($q) {
                $query->where('po_number', 'like', "%$q%")
                      ->orWhereHas('supplier', fn($qq) => $qq->where('name', 'like', "%$q%"));
            })
            ->when($status, fn($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(15);

        return view('owner.purchases.index', compact('purchases','q','status'));
    }

    public function create(): View
    {
        $suppliers = Supplier::orderBy('name')->get();
        return view('owner.purchases.create', compact('suppliers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_date' => ['required','date'],
            'supplier_id' => ['nullable','exists:suppliers,id'],
            'supplier_name' => ['nullable','string','max:255'],
            'is_paid' => ['sometimes','boolean'],
            'items' => ['required','array','min:1'],
            'items.*.product_id' => ['nullable','exists:products,id'],
            'items.*.product_name' => ['required','string','max:255'],
            'items.*.sku' => ['nullable','string','max:100'],
            'items.*.cost_price' => ['required','numeric','min:0'],
            'items.*.qty' => ['required','integer','min:1'],
            'items.*.discount' => ['nullable','numeric','min:0'],
        ]);

        $supplierId = $validated['supplier_id'] ?? null;
        if (!$supplierId) {
            // Auto-create supplier if supplier_name provided
            if (!empty($validated['supplier_name'])) {
                $supplier = Supplier::firstOrCreate(
                    ['name' => $validated['supplier_name']],
                    ['is_active' => true]
                );
                $supplierId = $supplier->id;
            } else {
                return back()->withErrors(['supplier_id' => 'Pilih supplier atau isi nama supplier.'])->withInput();
            }
        }

        DB::transaction(function () use ($validated, $supplierId) {
            $poNumber = $this->generatePoNumber();

            $subtotal = 0; $discountTotal = 0; $grandTotal = 0;
            foreach ($validated['items'] as $item) {
                $line = ((float)$item['cost_price'] * (int)$item['qty']);
                $disc = (float)($item['discount'] ?? 0);
                $subtotal += $line;
                $discountTotal += $disc;
            }
            $grandTotal = $subtotal - $discountTotal;

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'order_date' => $validated['order_date'],
                'supplier_id' => $supplierId,
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'grand_total' => $grandTotal,
                'status' => 'draft',
                'is_paid' => (bool)($validated['is_paid'] ?? false),
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                $line = ((float)$item['cost_price'] * (int)$item['qty']) - (float)($item['discount'] ?? 0);
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'sku' => $item['sku'] ?? null,
                    'cost_price' => $item['cost_price'],
                    'qty' => $item['qty'],
                    'discount' => $item['discount'] ?? 0,
                    'line_total' => $line,
                ]);
            }
        });

        return redirect()->route('owner.purchases.index')->with('success', 'Pembelian tersimpan sebagai draft.');
    }

    public function show(PurchaseOrder $purchase): View
    {
        $purchase->load(['supplier','items','creator','approver']);
        return view('owner.purchases.show', compact('purchase'));
    }

    public function submit(PurchaseOrder $purchase): RedirectResponse
    {
        if ($purchase->status !== 'draft') {
            return back()->withErrors(['status' => 'Hanya draft yang bisa diajukan.']);
        }
        $purchase->update(['status' => 'pending']);
        return back()->with('success', 'Pembelian diajukan untuk approval.');
    }

    public function approve(PurchaseOrder $purchase): RedirectResponse
    {
        if ($purchase->status !== 'pending') {
            return back()->withErrors(['status' => 'Hanya pending yang bisa di-approve.']);
        }
        $purchase->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => Carbon::now(),
        ]);
        return back()->with('success', 'Pembelian telah di-approve.');
    }

    public function receive(PurchaseOrder $purchase): RedirectResponse
    {
        if (!in_array($purchase->status, ['approved','pending'])) {
            return back()->withErrors(['status' => 'Hanya pending/approved yang bisa diterima.']);
        }

        DB::transaction(function () use ($purchase) {
            // Mark as received
            $purchase->update(['status' => 'received']);

            // Create Stock In
            $stockIn = StockIn::create([
                'stock_in_number' => $this->generateStockInNumber(),
                'purchase_order_id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'received_date' => Carbon::now()->toDateString(),
                'notes' => 'No. Pembelian: '.$purchase->po_number,
                'status' => 'posted',
                'received_by' => Auth::id(),
            ]);

            foreach ($purchase->items as $item) {
                StockInItem::create([
                    'stock_in_id' => $stockIn->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'sku' => $item->sku,
                    'qty' => $item->qty,
                ]);
            }
        });

        return back()->with('success', 'Barang diterima dan Stok Masuk dibuat.');
    }

    private function generatePoNumber(): string
    {
        $date = Carbon::now()->format('ymd');
        $seq = str_pad((string) (PurchaseOrder::whereDate('created_at', Carbon::today())->count() + 1), 4, '0', STR_PAD_LEFT);
        return 'PO'.$date.$seq;
    }

    private function generateStockInNumber(): string
    {
        $date = Carbon::now()->format('ymd');
        $seq = str_pad((string) (StockIn::whereDate('created_at', Carbon::today())->count() + 1), 4, '0', STR_PAD_LEFT);
        return 'IN'.$date.$seq;
    }
}