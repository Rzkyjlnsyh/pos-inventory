<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\PurchaseOrder;
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
public function dashboard(Request $request): View
{
    // DATE RANGE - Default to current month
    $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
    $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
    
    $start = Carbon::parse($startDate);
    $end = Carbon::parse($endDate);

    // === GET ALL SHIFTS dalam periode ===
    $shifts = Shift::with('user')
        ->where(function($query) use ($start, $end) {
            // Shift yang start_time dalam range, atau yang overlap dengan range
            $query->whereBetween('start_time', [$start, $end])
                  ->orWhere(function($q) use ($start, $end) {
                      $q->where('start_time', '<=', $end)
                        ->where(function($q2) use ($start) {
                            $q2->whereNull('end_time')
                               ->orWhere('end_time', '>=', $start);
                        });
                  });
        })
        ->orderBy('start_time', 'desc')
        ->get();

    // === CALCULATE TOTALS dari semua shift ===
    $totalShifts = $shifts->count();
    $activeShifts = $shifts->where('status', 'open')->count();
    $closedShifts = $shifts->where('status', 'closed')->count();

    // Total dari semua shift
    $totalInitialCash = $shifts->sum('initial_cash');
    $totalCashIncome = $shifts->sum('cash_total');
    $totalExpenses = $shifts->sum('expense_total');
    $totalFinalCash = $shifts->sum('final_cash');
    $totalDiscrepancy = $shifts->sum('discrepancy');

    // === DETAILED PAYMENT BREAKDOWN dari semua shift ===
    $allPayments = Payment::whereBetween('paid_at', [$start, $end])->get();
    
    $paymentBreakdown = [
        'cash' => [
            'lunas' => $allPayments->where('method', 'cash')
                ->filter(function($payment) {
                    $so = $payment->salesOrder;
                    return $so && $payment->category === 'pelunasan' && $so->payments->count() === 1;
                })->sum('amount'),
            'dp' => $allPayments->where('method', 'cash')->where('category', 'dp')->sum('amount'),
            'pelunasan' => $allPayments->where('method', 'cash')
                ->filter(function($payment) {
                    $so = $payment->salesOrder;
                    return $so && $payment->category === 'pelunasan' && $so->payments->count() > 1;
                })->sum('amount'),
        ],
        'transfer' => [
            'lunas' => $allPayments->where('method', 'transfer')
                ->filter(function($payment) {
                    $so = $payment->salesOrder;
                    return $so && $payment->category === 'pelunasan' && $so->payments->count() === 1;
                })->sum('amount'),
            'dp' => $allPayments->where('method', 'transfer')->where('category', 'dp')->sum('amount'),
            'pelunasan' => $allPayments->where('method', 'transfer')
                ->filter(function($payment) {
                    $so = $payment->salesOrder;
                    return $so && $payment->category === 'pelunasan' && $so->payments->count() > 1;
                })->sum('amount'),
        ]
    ];

    // Manual incomes & expenses dari semua shift
    $totalManualIncomes = Income::whereBetween('created_at', [$start, $end])->sum('amount');
    $totalManualExpenses = Expense::whereBetween('created_at', [$start, $end])->sum('amount');

    return view('finance.shift.dashboard', compact(
        'shifts',
        'totalShifts',
        'activeShifts', 
        'closedShifts',
        'totalInitialCash',
        'totalCashIncome',
        'totalExpenses',
        'totalFinalCash',
        'totalDiscrepancy',
        'paymentBreakdown',
        'totalManualIncomes',
        'totalManualExpenses',
        'startDate',
        'endDate'
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
    public function history(Request $request): View
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $shifts = Shift::with('user')
            ->where(function($query) use ($start, $end) {
                $query->whereBetween('start_time', [$start, $end])
                      ->orWhere(function($q) use ($start, $end) {
                          $q->where('start_time', '<=', $end)
                            ->where(function($q2) use ($start) {
                                $q2->whereNull('end_time')
                                   ->orWhere('end_time', '>=', $start);
                            });
                      });
            })
            ->orderBy('start_time', 'desc')
            ->paginate(20);

        return view('finance.shift.history', compact('shifts', 'startDate', 'endDate'));
    }

    /**
     * Detail Shift untuk Finance - Lihat detail (read-only)
     */
    public function show(Shift $shift): View
    {
        $shift->load(['user', 'expenses', 'incomes']);
        
        // Calculate detailed payments (pakai logic dari admin)
        $payments = Payment::where('created_by', $shift->user_id)
            ->where('created_at', '>=', $shift->start_time)
            ->where('created_at', '<=', $shift->end_time ?? now())
            ->with('salesOrder')
            ->get();

        $cashLunas = $cashDp = $cashPelunasan = $transferLunas = $transferDp = $transferPelunasan = 0;

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

        // Sales orders in this shift
        $salesOrders = SalesOrder::where('created_by', $shift->user_id)
            ->whereBetween('created_at', [$shift->start_time, $shift->end_time ?? now()])
            ->with(['customer', 'payments', 'items'])
            ->get();

        $paymentDetails = compact(
            'cashLunas', 'cashDp', 'cashPelunasan',
            'transferLunas', 'transferDp', 'transferPelunasan'
        );

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