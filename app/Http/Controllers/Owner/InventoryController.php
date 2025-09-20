<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockOpname;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        // Query stats untuk Overview
        $totalProducts = Product::count();
        $totalStockIns = StockIn::count();
        $totalStockInItems = StockIn::with('items')->get()->sum(function ($stockIn) {
            return $stockIn->items->sum('qty');
        });
        $pendingOpnames = StockOpname::where('status', 'draft')->count();
        $lowStockProducts = Product::where('stock_qty', '<', 10)->count();  // Threshold 10, bisa ubah
        $recentMovements = StockMovement::with('product')->orderBy('moved_at', 'desc')->take(5)->get();

        $prefix = $this->getViewPrefix();
        $view = "{$prefix}.inventory.index";
        // Return view dengan data
        return view($view, compact(
            'totalProducts',
            'totalStockIns',
            'totalStockInItems',
            'pendingOpnames',
            'lowStockProducts',
            'recentMovements'
        ));
    }
    protected function getViewPrefix()
    {
        if (request()->is('admin/*')) return 'admin';
        if (request()->is('finance/*')) return 'finance';
        if (request()->is('kepala-toko/*')) return 'kepala-toko';
        if (request()->is('editor/*')) return 'editor';
        return 'owner';
    }
}