<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\Payment;
use App\Models\SalesOrder;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ShiftHistoryExport;
use App\Exports\ShiftDetailExport;
use Carbon\Carbon;

class ShiftController extends Controller
{
    /**
     * Dashboard Supervisor Finance - Monitor ALL Active Shifts
     */
/**
 * Dashboard Finance - Focus on Financial Reporting & Analysis
 */
public function dashboard(): View
{
    $currentMonth = now()->month;
    $currentYear = now()->year;
    $lastMonth = now()->subMonth()->month;
    $lastMonthYear = now()->subMonth()->year;

    // === CASH FLOW ANALYSIS ===
    $cashFlow = [
        'current' => [
            'income' => Payment::whereYear('paid_at', $currentYear)
                            ->whereMonth('paid_at', $currentMonth)
                            ->sum('amount'),
            'expenses' => Expense::whereYear('created_at', $currentYear)
                            ->whereMonth('created_at', $currentMonth)
                            ->sum('amount')
        ],
        'previous' => [
            'income' => Payment::whereYear('paid_at', $lastMonthYear)
                            ->whereMonth('paid_at', $lastMonth)
                            ->sum('amount'),
            'expenses' => Expense::whereYear('created_at', $lastMonthYear)
                            ->whereMonth('created_at', $lastMonth)
                            ->sum('amount')
        ]
    ];

    // === SALES PERFORMANCE ===
    $salesPerformance = [
        'totalSales' => SalesOrder::whereYear('created_at', $currentYear)
                            ->whereMonth('created_at', $currentMonth)
                            ->count(),
        'totalRevenue' => SalesOrder::whereYear('created_at', $currentYear)
                            ->whereMonth('created_at', $currentMonth)
                            ->sum('grand_total'),
        'averageTicket' => SalesOrder::whereYear('created_at', $currentYear)
                            ->whereMonth('created_at', $currentMonth)
                            ->avg('grand_total') ?? 0
    ];

    // === OUTSTANDING PAYMENTS ===
    $outstandingPayments = SalesOrder::where('payment_status', 'dp')
        ->with(['customer', 'payments'])
        ->whereHas('payments', function($query) {
            $query->where('created_at', '>=', now()->subMonths(3)); // Last 3 months
        })
        ->get()
        ->map(function($so) {
            return [
                'so_number' => $so->so_number,
                'customer' => $so->customer->name ?? 'Guest',
                'grand_total' => $so->grand_total,
                'paid_total' => $so->paid_total,
                'remaining' => $so->remaining_amount,
                'days_old' => $so->created_at->diffInDays(now())
            ];
        })
        ->sortByDesc('remaining')
        ->take(10);

    $totalOutstanding = $outstandingPayments->sum('remaining');

    // === TOP PRODUCTS ===
    $topProducts = \App\Models\SalesOrderItem::selectRaw('
            product_name,
            SUM(qty) as total_qty,
            SUM(line_total) as total_revenue
        ')
        ->whereHas('salesOrder', function($query) use ($currentMonth, $currentYear) {
            $query->whereYear('created_at', $currentYear)
                  ->whereMonth('created_at', $currentMonth);
        })
        ->groupBy('product_name')
        ->orderByDesc('total_revenue')
        ->limit(10)
        ->get();

    // === PAYMENT METHOD ANALYSIS ===
    $paymentMethods = Payment::selectRaw('
            method,
            COUNT(*) as transaction_count,
            SUM(amount) as total_amount
        ')
        ->whereYear('paid_at', $currentYear)
        ->whereMonth('paid_at', $currentMonth)
        ->groupBy('method')
        ->get();

    return view('finance.shift.dashboard', compact(
        'cashFlow',
        'salesPerformance', 
        'outstandingPayments',
        'totalOutstanding',
        'topProducts',
        'paymentMethods'
    ));
}

    /**
     * Calculate payments for a shift (helper function)
     */
    private function calculateShiftPayments(Shift $shift): array
    {
        $cashLunas = $cashDp = $cashPelunasan = $transferLunas = $transferDp = $transferPelunasan = 0;

        $payments = Payment::where('created_by', $shift->user_id)
            ->where('created_at', '>=', $shift->start_time)
            ->where('created_at', '<=', $shift->end_time ?? now())
            ->with('salesOrder')
            ->get();

        foreach ($payments as $payment) {
            $so = $payment->salesOrder;
            $isLunasSekaliBayar = ($payment->category === 'pelunasan' && $so->payments->count() === 1);
            
            if ($payment->method === 'cash') {
                if ($isLunasSekaliBayar) {
                    $cashLunas += $payment->amount;
                } elseif ($payment->category === 'dp') {
                    $cashDp += $payment->amount;
                } else {
                    $cashPelunasan += $payment->amount;
                }
            } elseif ($payment->method === 'transfer') {
                if ($isLunasSekaliBayar) {
                    $transferLunas += $payment->amount;
                } elseif ($payment->category === 'dp') {
                    $transferDp += $payment->amount;
                } else {
                    $transferPelunasan += $payment->amount;
                }
            } elseif ($payment->method === 'split') {
                if ($isLunasSekaliBayar) {
                    $cashLunas += $payment->cash_amount;
                    $transferLunas += $payment->transfer_amount;
                } elseif ($payment->category === 'dp') {
                    $cashDp += $payment->cash_amount;
                    $transferDp += $payment->transfer_amount;
                } else {
                    $cashPelunasan += $payment->cash_amount;
                    $transferPelunasan += $payment->transfer_amount;
                }
            }
        }

        $cashTotal = $cashLunas + $cashDp + $cashPelunasan;
        $transferTotal = $transferLunas + $transferDp + $transferPelunasan;

        return compact(
            'cashLunas', 'cashDp', 'cashPelunasan',
            'transferLunas', 'transferDp', 'transferPelunasan',
            'cashTotal', 'transferTotal'
        );
    }

    /**
     * Riwayat Shift untuk Finance - Lihat SEMUA shift
     */
    public function history(): View
    {
        $shifts = Shift::with('user')
            ->orderBy('start_time', 'desc')
            ->paginate(15);

        return view('finance.shift.history', compact('shifts'));
    }

    /**
     * Detail Shift untuk Finance - Lihat detail (read-only)
     */
    public function show(Shift $shift): View
    {
        $shift->load(['user', 'expenses', 'incomes']);
        
        // Calculate detailed payments
        $paymentDetails = $this->calculateShiftPayments($shift);
        
        // Sales orders in this shift
        $salesOrders = SalesOrder::where('created_by', $shift->user_id)
            ->whereBetween('created_at', [$shift->start_time, $shift->end_time ?? now()])
            ->with(['customer', 'payments', 'items'])
            ->get();

        return view('finance.shift.show', compact(
            'shift', 
            'salesOrders', 
            'paymentDetails'
        ));
    }

    /**
     * Export Excel untuk Finance
     */
    public function export()
    {
        return Excel::download(new ShiftHistoryExport, 'shift_history_' . date('Ymd_His') . '.xlsx');
    }

    /**
     * Export Detail Excel untuk Finance
     */
    public function exportDetail(Shift $shift)
    {
        return Excel::download(new ShiftDetailExport($shift), 'shift_detail_' . $shift->id . '_' . date('Ymd_His') . '.xlsx');
    }
}