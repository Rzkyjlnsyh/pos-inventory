<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Product;
use App\Models\StockMovement;
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
        $group = $request->get('group');
        $type = $request->get('type'); // tambahan untuk filter tipe

        $purchases = PurchaseOrder::with(['supplier','creator','approver'])
            ->when($q, function ($query) use ($q) {
                $query->where('po_number', 'like', "%$q%")
                      ->orWhereHas('supplier', fn($qq) => $qq->where('name', 'like', "%$q%"));
            })
            ->when($type, fn($query) => $query->where('purchase_type', $type))
            ->when($group, function ($query) use ($group) {
                return match ($group) {
                    'todo' => $query->whereIn('status', ['draft','pending']),
                    'approved' => $query->where('status', 'approved'),
                    'in_progress' => $query->whereIn('status', ['payment', 'kain_diterima', 'printing', 'jahit']),
                    'completed' => $query->where('status', 'selesai'),
                    'cancelled' => $query->where('status', 'canceled'),
                    default => $query,
                };
            })
            ->when($status, fn($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(15);

        return view('owner.purchases.index', compact('purchases','q','status','group','type'));
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
            'purchase_type' => ['required','in:kain,produk_jadi'], // validasi tipe pembelian
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
                'purchase_type' => $validated['purchase_type'], // simpan tipe pembelian
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'grand_total' => $grandTotal,
                'status' => PurchaseOrder::STATUS_DRAFT,
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
        $purchase->load([
            'supplier','items','creator','approver','receiver',
            'paymentProcessor', 'kainReceiver', 'printer', 'tailor', 'finisher'
        ]);
        return view('owner.purchases.show', compact('purchase'));
    }

    public function submit(PurchaseOrder $purchase): RedirectResponse
    {
        if ($purchase->status !== PurchaseOrder::STATUS_DRAFT) {
            return back()->withErrors(['status' => 'Hanya draft yang bisa diajukan.']);
        }
        
        $purchase->status = PurchaseOrder::STATUS_PENDING;
        $purchase->save();
        
        return back()->with('success', 'Pembelian diajukan untuk approval.');
    }

    public function approve(Request $request, PurchaseOrder $purchase): RedirectResponse
    {
        if ($purchase->status !== PurchaseOrder::STATUS_PENDING) {
            return back()->withErrors(['status' => 'Hanya pending yang bisa di-approve.']);
        }
    
        $purchase->update([
            'status' => PurchaseOrder::STATUS_APPROVED,
            'approved_by' => Auth::id(),
            'approved_at' => Carbon::now(),
        ]);
    
        return back()->with('success', 'Pembelian telah di-approve.');
    }
    public function payment(Request $request, PurchaseOrder $purchase): RedirectResponse
    {
        if ($purchase->status !== PurchaseOrder::STATUS_APPROVED) {
            return back()->withErrors(['status' => 'Hanya approved yang bisa diproses pembayaran.']);
        }
    
        $validated = $request->validate([
            'invoice_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'payment_proof_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);
    
        $invoicePath = $request->file('invoice_file')->store('purchase_orders/invoices', 'public');
        $paymentProofPath = $request->file('payment_proof_file')->store('purchase_orders/payments', 'public');
    
        $purchase->update([
            'status' => PurchaseOrder::STATUS_PAYMENT,
            'payment_by' => Auth::id(),
            'payment_at' => Carbon::now(),
            'invoice_file' => $invoicePath,
            'payment_proof_file' => $paymentProofPath,
        ]);
    
        return back()->with('success', 'Pembayaran telah diproses dengan file faktur dan bukti pembayaran.');
    }

    // Method baru untuk update status workflow
    public function updateWorkflowStatus(Request $request, PurchaseOrder $purchase): RedirectResponse
    {
        $validated = $request->validate([
            'new_status' => 'required|string',
        ]);

        $success = $purchase->updateStatus($validated['new_status'], Auth::id());
        
        if (!$success) {
            return back()->withErrors(['status' => 'Status tidak valid atau tidak bisa diupdate.']);
        }

        // Handle khusus untuk selesai - update stock untuk kedua tipe
        if ($validated['new_status'] === PurchaseOrder::STATUS_SELESAI) {
            if ($purchase->isKainType()) {
                $this->handleKainSelesai($purchase);
            } elseif ($purchase->isProdukJadiType()) {
                $this->handleProdukJadiSelesai($purchase);
            }
        }

        $statusLabel = $purchase->getStatusLabel();
        return back()->with('success', "Status berhasil diupdate ke: {$statusLabel}");
    }

    private function handleKainSelesai(PurchaseOrder $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            // Create Stock In untuk kain yang sudah selesai (printing + jahit)
            $stockIn = StockIn::create([
                'stock_in_number' => $this->generateStockInNumber(),
                'purchase_order_id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'received_date' => Carbon::now()->toDateString(),
                'notes' => 'Produk kain selesai dari PO: ' . $purchase->po_number,
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

                if ($item->product_id) {
                    $product = $item->product;
                    $initial = $product->stock_qty ?? 0;
                    $product->stock_qty = $initial + $item->qty;
                    $product->save();

                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'INCOMING',
                        'ref_code' => $stockIn->stock_in_number,
                        'initial_qty' => $initial,
                        'qty_in' => $item->qty,
                        'qty_out' => 0,
                        'final_qty' => $product->stock_qty,
                        'user_id' => Auth::id(),
                        'notes' => 'Produk kain selesai (PO: ' . $purchase->po_number . ')',
                        'moved_at' => Carbon::now(),
                    ]);
                }
            }
        });
    }

    private function handleProdukJadiSelesai(PurchaseOrder $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            // Create Stock In untuk produk jadi
            $stockIn = StockIn::create([
                'stock_in_number' => $this->generateStockInNumber(),
                'purchase_order_id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'received_date' => Carbon::now()->toDateString(),
                'notes' => 'Produk jadi diterima dari PO: ' . $purchase->po_number,
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

                if ($item->product_id) {
                    $product = $item->product;
                    $initial = $product->stock_qty ?? 0;
                    $product->stock_qty = $initial + $item->qty;
                    $product->save();

                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'INCOMING',
                        'ref_code' => $stockIn->stock_in_number,
                        'initial_qty' => $initial,
                        'qty_in' => $item->qty,
                        'qty_out' => 0,
                        'final_qty' => $product->stock_qty,
                        'user_id' => Auth::id(),
                        'notes' => 'Produk jadi diterima (PO: ' . $purchase->po_number . ')',
                        'moved_at' => Carbon::now(),
                    ]);
                }
            }
        });
    }

    // Method lama tetap dipakai untuk backward compatibility
    public function receive(PurchaseOrder $purchase): RedirectResponse
    {
        if (!in_array($purchase->status, ['approved','pending'])) {
            return back()->withErrors(['status' => 'Hanya pending/approved yang bisa diterima.']);
        }

        DB::transaction(function () use ($purchase) {
            $purchase->update([
                'status' => 'received',
                'received_at' => Carbon::now(),
                'received_by' => Auth::id(),
            ]);

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

                if ($item->product_id) {
                    $product = $item->product;
                    $initial = $product->stock_qty ?? 0;
                    $product->stock_qty = $initial + $item->qty;
                    $product->save();

                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'INCOMING',
                        'ref_code' => $stockIn->stock_in_number,
                        'initial_qty' => $initial,
                        'qty_in' => $item->qty,
                        'qty_out' => 0,
                        'final_qty' => $product->stock_qty,
                        'user_id' => Auth::id(),
                        'notes' => 'Pembelian diterima (PO: '.$purchase->po_number.')',
                        'moved_at' => Carbon::now(),
                    ]);
                }
            }
        });

        return back()->with('success', 'Barang diterima, stok produk diperbarui, dan pergerakan stok dicatat.');
    }

    public function cancel(PurchaseOrder $purchase): RedirectResponse
    {
        if ($purchase->status === PurchaseOrder::STATUS_CANCELLED) {
            return back()->with('error', 'Pembelian sudah dibatalkan.');
        }

        // Tidak bisa cancel jika sudah masuk ke production workflow
        $productionStatuses = [
            PurchaseOrder::STATUS_PAYMENT,
            PurchaseOrder::STATUS_KAIN_DITERIMA,
            PurchaseOrder::STATUS_PRINTING,
            PurchaseOrder::STATUS_JAHIT,
            PurchaseOrder::STATUS_SELESAI
        ];

        if (in_array($purchase->status, $productionStatuses)) {
            return back()->with('error', 'Tidak bisa membatalkan pembelian yang sudah masuk ke proses produksi.');
        }

        $purchase->cancel();

        return back()->with('success', 'Pembelian berhasil dibatalkan.');
    }

    public function return(Request $request, PurchaseOrder $purchase): \Illuminate\Http\JsonResponse
    {
        if (!in_array($purchase->status, [PurchaseOrder::STATUS_SELESAI, 'received'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pembelian yang sudah selesai yang bisa diretur.'
            ], 400);
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Buat purchase return
            $return = PurchaseReturn::create([
                'purchase_order_id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'return_date' => now(),
                'reason' => $validated['reason'],
                'created_by' => Auth::id(),
            ]);

            $totalAmount = 0;

            // Process return items
            foreach ($validated['items'] as $itemData) {
                $purchaseItem = $purchase->items()
                    ->where('product_id', $itemData['product_id'])
                    ->first();

                if (!$purchaseItem || $itemData['quantity'] > $purchaseItem->qty) {
                    throw new \Exception('Quantity retur tidak valid untuk produk: ' . $itemData['product_id']);
                }

                $returnItem = PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'product_id' => $itemData['product_id'],
                    'qty' => $itemData['quantity'],
                    'price' => $purchaseItem->cost_price,
                    'total' => $purchaseItem->cost_price * $itemData['quantity']
                ]);

                $totalAmount += $returnItem->total;

                // Kurangi stok
                $product = Product::find($itemData['product_id']);
                $initial = $product->stock_qty;
                $product->stock_qty -= $itemData['quantity'];
                $product->save();

                // Catat stock movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'OUTGOING',
                    'ref_code' => 'RET-' . $return->id,
                    'initial_qty' => $initial,
                    'qty_in' => 0,
                    'qty_out' => $itemData['quantity'],
                    'final_qty' => $product->stock_qty,
                    'user_id' => Auth::id(),
                    'notes' => 'Retur pembelian: ' . $purchase->po_number . ' - ' . $validated['reason'],
                    'moved_at' => now(),
                ]);
            }

            $purchase->update(['status' => 'returned']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Retur pembelian berhasil diproses'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses retur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getItems(PurchaseOrder $purchase)
    {
        $items = $purchase->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'quantity' => $item->qty,
                'cost_price' => $item->cost_price
            ];
        });

        return response()->json($items);
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