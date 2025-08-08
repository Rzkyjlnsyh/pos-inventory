<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\StockIn;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockInController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->get('q');
        $stockIns = StockIn::with(['supplier','purchaseOrder','items'])
            ->when($q, fn($query) => $query->where('stock_in_number','like',"%$q%"))
            ->orderByDesc('id')
            ->paginate(15);

        return view('owner.inventory.stock_ins.index', compact('stockIns','q'));
    }
}