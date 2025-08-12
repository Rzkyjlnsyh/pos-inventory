<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\StockIn;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->get('q');
        $stockIns = StockIn::with(['items','supplier','receiver','purchaseOrder'])
            ->when($q, fn($query) => $query->where('stock_in_number','like',"%$q%"))
            ->orderByDesc('id')
            ->paginate(15);

        return view('owner.inventory.movements.index', compact('stockIns','q'));
    }
}