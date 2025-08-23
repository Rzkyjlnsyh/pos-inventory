<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\StockIn;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockInController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->get('q');
        $stockIns = StockIn::with(['supplier','purchaseOrder','items','receiver'])
            ->when($q, fn($query) => $query->where('stock_in_number','like',"%$q%"))
            ->orderByDesc('id')
            ->paginate(15);

        return view('owner.inventory.stock_ins.index', compact('stockIns','q'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'stock_in_number' => 'required|string|max:50|unique:stock_ins,stock_in_number',
            'supplier_id'     => 'required|exists:suppliers,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'received_by'     => 'required|exists:users,id',
        ]);

        $stockIn = StockIn::create($validated);

        // Catat ke tabel stock_movements
        StockMovement::create([
            'document_type' => 'stock_in',
            'document_id'   => $stockIn->id,
            'movement_type' => 'in',
            'reference_number' => $stockIn->stock_in_number,
            'user_id'       => auth()->id(),
        ]);

        return redirect()->route('owner.inventory.stock_ins.index')
            ->with('success', 'Stock In berhasil ditambahkan & dicatat di log pergerakan stok.');
    }
}
