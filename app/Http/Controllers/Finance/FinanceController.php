<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\PurchaseOrder;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class FinanceController extends Controller
{
    /**
     * Dashboard Utama Finance - Income Statement
     */
    public function dashboard(Request $request): View
    {
        // 1. TANGGAL - PASTIKAN SELALU ADA
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // 2. HITUNG OMSET - Total Penjualan + Pemasukan Manual
        $totalSales = SalesOrder::whereBetween('created_at', [$start, $end])->sum('grand_total') ?? 0;
        $manualIncome = Income::whereBetween('created_at', [$start, $end])->sum('amount') ?? 0;
        $omset = $totalSales + $manualIncome;

        // 3. HITUNG HPP - Total Pembelian SELESAI
        $hpp = PurchaseOrder::whereBetween('created_at', [$start, $end])
            ->where('status', 'selesai') // PASTIKAN NAMA STATUSNYA BENER
            ->sum('grand_total') ?? 0;

        // 4. HITUNG OPERASIONAL - Pengeluaran Manual
        $operasional = Expense::whereBetween('created_at', [$start, $end])->sum('amount') ?? 0;

        // 5. HITUNG PROFIT
        $profit = $omset - $hpp - $operasional;

        // 6. DATA TAMBAHAN 
        $salesByPaymentMethod = Payment::whereBetween('paid_at', [$start, $end])
            ->selectRaw('method, SUM(amount) as total_amount, COUNT(*) as transaction_count')
            ->groupBy('method')
            ->get();

        $recentSales = SalesOrder::with(['customer', 'payments'])
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

// === PRODUK TERLARIS - SIMPLE VERSION ===
$bestSellingProducts = \App\Models\SalesOrderItem::selectRaw('
        product_id,
        products.name as product_name,
        products.sku as product_sku,
        SUM(sales_order_items.qty) as total_terjual
    ')
    ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
    ->join('products', 'sales_order_items.product_id', '=', 'products.id')
    ->whereBetween('sales_orders.created_at', [$start, $end])
    ->where('sales_orders.status', 'selesai')
    ->groupBy('product_id', 'products.name', 'products.sku')
    ->orderBy('total_terjual', 'desc')
    ->limit(5)
    ->get();

        $omsetGrowth = 0; // Sementara 0 dulu

        // 7. KIRIM SEMUA DATA KE VIEW
        return view('finance.dashboard', [
            'omset' => $omset,
            'totalSales' => $totalSales,
            'manualIncome' => $manualIncome,
            'hpp' => $hpp,
            'operasional' => $operasional,
            'profit' => $profit,
            'salesByPaymentMethod' => $salesByPaymentMethod,
            'recentSales' => $recentSales,
            'omsetGrowth' => $omsetGrowth,
            'startDate' => $startDate,
            'bestSellingProducts' => $bestSellingProducts,
            'endDate' => $endDate
        ]);
    }

    /**
     * Index page - redirect to dashboard
     */
    public function index(): View
    {
        return $this->dashboard(new Request());
    }
}