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
        // STEP 1: Filter hanya items dengan qty > 0
        $filteredItems = array_filter($request->items, function($item) {
            return ($item['qty'] ?? 0) > 0;
        });
    
        // STEP 2: Validasi minimal 1 item dengan qty > 0
        if (empty($filteredItems)) {
            return back()->with('error', 'Pilih minimal 1 item dengan quantity retur > 0')->withInput();
        }
    
        // STEP 3: Validasi data
        $validated = $request->validate([
            'return_date' => 'required|date',
            'reason' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:0', // UBAH min:1 JADI min:0
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
                'restock' => true,
                'return_type' => $validated['return_type'], // TAMBAH INI
                'created_by' => Auth::id(),
            ]);
    
            $subtotal = 0;
    
            // STEP 4: HANYA PROCESS ITEMS DENGAN QTY > 0
            foreach ($filteredItems as $itemData) {
                $purchaseItem = $purchase->items()
                    ->where('product_id', $itemData['product_id'])
                    ->first();
    
                if (!$purchaseItem || $itemData['qty'] > $purchaseItem->qty) {
                    throw new \Exception('Quantity retur tidak valid untuk produk: ' . $itemData['product_id']);
                }
    
                $total = ($purchaseItem->cost_price * $itemData['qty']) - ($purchaseItem->discount ?? 0);
    
                $returnItem = $return->items()->create([
                    'product_id' => $itemData['product_id'],
                    'qty' => $itemData['qty'], // SEKARANG QTY DARI INPUT FORM!
                    'price' => $purchaseItem->cost_price,
                    'discount' => $purchaseItem->discount ?? 0,
                    'total' => $total,
                    'restock' => true,
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
            return back()->with('error', 'Gagal membuat retur: ' . $e->getMessage())->withInput();
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
    
            logger('=== DEBUG CONFIRM RETURN ===');
            logger('Return ID: ' . $purchaseReturn->id);
            logger('Items count: ' . $purchaseReturn->items->count());
    
            // 1. Update return status
            $purchaseReturn->update(['status' => 'confirmed']);
            logger('Return status updated to confirmed');
    
            // 2. Manual stock reduction & movement
// 2. Manual stock reduction & movement - UPDATE LOGIC INI
foreach ($purchaseReturn->items as $item) {
    $product = Product::find($item->product_id);
    
    if ($product) {
        // TENTUKAN TYPE BERDASARKAN return_type
        $type = $purchaseReturn->return_type === 'extra' ? 'PRODUCTION_EXTRA' : 'PURCHASE_RETURN';
        
        if ($purchaseReturn->return_type === 'extra') {
            // PRODUKSI LEBIH - TAMBAH STOK
            $finalQty = $product->stock_qty + $item->qty;
            DB::table('products')
                ->where('id', $product->id)
                ->increment('stock_qty', $item->qty);
        } else {
            // RETURN RUSAK - KURANGI STOK
            $finalQty = $product->stock_qty - $item->qty;
            DB::table('products')
                ->where('id', $product->id)
                ->decrement('stock_qty', $item->qty);
        }

        // Manual create stock movement
        DB::table('stock_movements')->insert([
            'product_id' => $product->id,
            'type' => $type,
            'ref_code' => $purchaseReturn->return_number,
            'initial_qty' => $product->stock_qty,
            'qty_in' => $purchaseReturn->return_type === 'extra' ? $item->qty : 0,
            'qty_out' => $purchaseReturn->return_type === 'extra' ? 0 : $item->qty,
            'final_qty' => $finalQty,
            'user_id' => Auth::id(),
            'notes' => $purchaseReturn->return_type === 'extra' 
                ? 'Produksi lebih: ' . $purchaseReturn->purchaseOrder->po_number
                : 'Retur pembelian: ' . $purchaseReturn->purchaseOrder->po_number,
            'moved_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
    
            // 3. FIX: Gunakan method yang sudah ada dulu
            logger('Updating PO status dengan smart logic...');
            $purchaseReturn->purchaseOrder->updateReturnStatus();
            logger('PO status updated dengan smart logic: ' . $purchaseReturn->purchaseOrder->status);
    
            DB::commit();
            logger('=== CONFIRM SUCCESS ===');
            return back()->with('success', 'Retur dikonfirmasi dan stok dikurangi');
    
        } catch (\Exception $e) {
            DB::rollBack();
            logger('=== CONFIRM ERROR: ' . $e->getMessage() . ' ===');
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function cancel(PurchaseReturn $purchaseReturn)
    {
        try {
            DB::beginTransaction();
    
            // Update status return jadi cancelled
            $purchaseReturn->update(['status' => 'cancelled']);
            
            // UPDATE INI: Kembalikan status PO ke logic smart
            $purchaseReturn->purchaseOrder->updateReturnStatus();
    
            DB::commit();
            return back()->with('success', 'Retur dibatalkan dan status PO dikembalikan');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membatalkan retur: ' . $e->getMessage());
        }
    }
}