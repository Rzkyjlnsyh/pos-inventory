<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockOpname;
use App\Models\StockMovement;
use App\Models\StockOpnameItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    public function index()
    {
        $stockOpnames = StockOpname::with([
            'creator:id,name,email',
            'approver:id,name,email',
            'items.product:id,name'
        ])->latest()->paginate(10);

        return view('owner.inventory.stock-opnames.index', compact('stockOpnames'));
    }

    public function create()
    {
        $products = Product::select('id', 'name', 'stock_qty')->get();
        $autoNumber = $this->generateDocumentNumber();
        
        return view('owner.inventory.stock-opnames.create', [
            'products' => $products,
            'autoNumber' => $autoNumber
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_number' => 'required|unique:stock_opnames',
'date' => ['required', 'date', function ($attribute, $value, $fail) {
    if ($value !== date('Y-m-d')) {
        $fail('Tanggal harus hari ini.');
    }
}],
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.system_qty' => 'required|numeric',
            'items.*.actual_qty' => 'required|numeric|min:0',
        ]);
    
        DB::transaction(function () use ($validated) {
            // Buat stock opname
            $stockOpname = StockOpname::create([
                'document_number' => $validated['document_number'],
                'date' => $validated['date'],
                'notes' => $validated['notes'],
                'status' => 'draft',
                'user_id' => auth()->id()
            ]);
    
            // Simpan item-itemnya
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                
                // Gunakan stock_qty aktual dari database, bukan dari input form
                $systemQty = $product->stock_qty;
                
                $stockOpname->items()->create([
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'sku' => $product->sku ?? null,
                    'system_qty' => $systemQty, // Gunakan nilai aktual dari DB
                    'actual_qty' => $item['actual_qty'],
                    'difference' => $item['actual_qty'] - $systemQty
                ]);
            }
        });
    
        return redirect()->route('owner.inventory.stock-opnames.index')
            ->with('success', 'Stock opname berhasil dibuat');
    }

    public function approve($id)
    {
        $stockOpname = StockOpname::with('items.product')->findOrFail($id);
        
        DB::transaction(function () use ($stockOpname) {
            // Update status dan approved_by
            $stockOpname->update([
                'status' => 'approved',
                'approved_by' => auth()->id()
            ]);
            
            // Update stok produk dan catat stock movement
            foreach ($stockOpname->items as $item) {
                $product = $item->product;
                
                if ($product) {
                    $initialQty = $product->stock_qty;
                    $finalQty = $item->actual_qty;
                    
                    // Update stok produk
                    $product->update(['stock_qty' => $finalQty]);
                    
                    // Hitung selisih untuk stock movement
                    $qtyIn = $finalQty > $initialQty ? $finalQty - $initialQty : 0;
                    $qtyOut = $finalQty < $initialQty ? $initialQty - $finalQty : 0;
                    
                    // Catat stock movement
                    StockMovement::record([
                        'product_id' => $product->id,
                        'type' => StockMovement::OPNAME,
                        'ref_code' => $stockOpname->document_number,
                        'qty_in' => $qtyIn,
                        'qty_out' => $qtyOut,
                        'user_id' => auth()->id(),
                        'notes' => 'Stock opname: ' . $stockOpname->document_number,
                        'moved_at' => now(),
                    ]);
                }
            }
        });
    
        return redirect()->route('owner.inventory.stock-opnames.index')
            ->with('success', 'Stock Opname berhasil disetujui dan stok produk diperbarui');
    }

    public function destroy($id)
    {
        $stockOpname = StockOpname::findOrFail($id);
        
        if ($stockOpname->status === 'approved') {
            return back()->with('error', 'Tidak bisa menghapus Stock Opname yang sudah disetujui');
        }
    
        DB::transaction(function () use ($stockOpname) {
            $stockOpname->items()->delete();
            $stockOpname->delete();
        });
    
        return redirect()->route('owner.inventory.stock-opnames.index')
            ->with('success', 'Stock Opname berhasil dihapus');
    }
    public function show($id)
{
    $stockOpname = StockOpname::with([
        'creator:id,name,email',
        'approver:id,name,email',
        'items.product:id,name'
    ])->findOrFail($id);

    return view('owner.inventory.stock-opnames.show', compact('stockOpname'));
}
private function generateDocumentNumber()
{
    $prefix = 'SO';
    $datePart = date('Ymd'); // Misal: 20250905

    $lastSO = StockOpname::where('document_number', 'like', $prefix . $datePart . '%')
                ->orderBy('document_number', 'desc')
                ->first();

    $number = 1;
    if ($lastSO) {
        $lastNumber = (int) substr($lastSO->document_number, -3);
        $number = $lastNumber + 1;
    }

    return $prefix . $datePart . str_pad($number, 3, '0', STR_PAD_LEFT);
}
}