<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        // Set default tanggal ke hari ini jika tidak ada filter
        $from = $request->input('from', Carbon::today()->toDateString());
        $to = $request->input('to', Carbon::today()->toDateString());

        // Query untuk mendapatkan data pergerakan stok yang digroup
        $stockMovements = StockMovement::select([
                'product_id',
                DB::raw('DATE(moved_at) as movement_date'),
                DB::raw('SUM(qty_in) as total_in'),
                DB::raw('SUM(qty_out) as total_out'),
                DB::raw('MAX(initial_qty) as initial_qty'),
                DB::raw('MAX(final_qty) as final_qty'),
                DB::raw('COUNT(*) as transaction_count')
            ])
            ->with('product')
            ->whereDate('moved_at', '>=', $from)
            ->whereDate('moved_at', '<=', $to)
            ->groupBy('product_id', DB::raw('DATE(moved_at)'))
            ->orderBy('movement_date', 'desc')
            ->orderBy('product_id')
            ->paginate(15);

        return view('owner.inventory.stock_movements.index', compact('stockMovements', 'from', 'to'));
    }

    public function getProductMovements($productId, $date)
    {
        // Ambil detail pergerakan stok untuk produk tertentu di tanggal tertentu
        $movements = StockMovement::with(['product', 'user'])
            ->where('product_id', $productId)
            ->whereDate('moved_at', $date)
            ->orderBy('moved_at', 'desc')
            ->get();

        $product = Product::find($productId);

        return response()->json([
            'product' => $product->name ?? 'Unknown Product',
            'date' => $date,
            'movements' => $movements
        ]);
    }
}