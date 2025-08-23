<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\PurchaseReturn;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        $returns = PurchaseReturn::with(['purchaseOrder', 'supplier', 'creator'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('owner.purchases.returns.index', compact('returns'));
    }

    public function create(PurchaseOrder $purchase)
    {
        $purchase->load(['items.product', 'supplier']);
        return view('owner.purchases.returns.create', compact('purchase'));
    }

    public function store(Request $request, PurchaseOrder $purchase)
    {
        $validated = $request->validate([
            'return_date' => 'required|date',
            'reason' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);
    
        try {
            DB::beginTransaction();
    
            $returnNumber = 'RET' . Carbon::now()->format('ymd') . str_pad(PurchaseReturn::count() + 1, 4, '0', STR_PAD_LEFT);
    
            $return = PurchaseReturn::create([
                'return_number' => $returnNumber,
                'purchase_order_id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'return_date' => $validated['return_date'],
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
                'restock' => true, // FORCE SELALU TRUE
                'created_by' => Auth::id(),
            ]);

            $subtotal = 0;

            foreach ($validated['items'] as $itemData) {
                $purchaseItem = $purchase->items()
                    ->where('product_id', $itemData['product_id'])
                    ->first();

                if (!$purchaseItem || $itemData['qty'] > $purchaseItem->qty) {
                    throw new \Exception('Quantity retur tidak valid');
                }

                $total = ($purchaseItem->cost_price * $itemData['qty']) - ($purchaseItem->discount ?? 0);

                $returnItem = $return->items()->create([
                    'product_id' => $itemData['product_id'],
                    'qty' => $itemData['qty'],
                    'price' => $purchaseItem->cost_price,
                    'discount' => $purchaseItem->discount ?? 0,
                    'total' => $total,
                    'restock' => $itemData['restock'] ?? false,
                ]);

                $subtotal += $total;
            }

            $return->update([
                'subtotal' => $subtotal,
                'total_amount' => $subtotal
            ]);

            DB::commit();

            return redirect()->route('owner.purchase-returns.show', $return)
                ->with('success', 'Retur berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat retur: ' . $e->getMessage());
        }
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['purchaseOrder', 'supplier', 'items.product', 'creator']);
        return view('owner.purchases.returns.show', compact('purchaseReturn'));
    }

    public function confirm(PurchaseReturn $purchaseReturn)
    {
        try {
            DB::beginTransaction();
    
            $purchaseReturn->update(['status' => 'confirmed']);
            $purchaseReturn->purchaseOrder->update(['status' => 'returned']);
    
            foreach ($purchaseReturn->items as $item) {
                // HANYA kurangi stok jika restock = true
                if ($item->restock) {
                    $product = $item->product;
                    $initial = $product->stock_qty;
                    $product->stock_qty -= $item->qty;
                    $product->save();
    
                    StockMovement::record([
                        'product_id' => $product->id,
                        'type' => StockMovement::PURCHASE_RETURN,
                        'ref_code' => $purchaseReturn->return_number,
                        'qty_out' => $item->qty,
                        'user_id' => Auth::id(),
                        'notes' => 'Retur pembelian: ' . $purchaseReturn->purchaseOrder->po_number,
                        'moved_at' => now(),
                    ]);
                }
            }
    
            DB::commit();
            return back()->with('success', 'Retur dikonfirmasi');
    
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengonfirmasi retur: ' . $e->getMessage());
        }
    }

    public function cancel(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->update(['status' => 'cancelled']);
        return back()->with('success', 'Retur dibatalkan');
    }
}