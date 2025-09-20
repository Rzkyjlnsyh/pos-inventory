<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\SalesOrder;
use App\Models\Expense;
use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ShiftHistoryExport;
use App\Exports\ShiftDetailExport;

class ShiftController extends Controller
{
    /**
     * Riwayat Shift untuk Finance
     * Lihat SEMUA shift (read-only) + bisa export
     */
    public function history(): View
    {
        $shifts = Shift::with('user')->orderBy('start_time', 'desc')->paginate(10);
        return view('finance.shift.history', compact('shifts'));
    }

    /**
     * Detail Shift untuk Finance
     * Lihat detail (read-only) + bisa export
     */
    public function show(Shift $shift): View
    {
        $salesOrders = SalesOrder::where('created_by', $shift->user_id)
            ->whereBetween('created_at', [$shift->start_time, $shift->end_time ?? now()])
            ->with('payments')
            ->get();

        $expenses = Expense::where('shift_id', $shift->id)->get();
        $incomes = Income::where('shift_id', $shift->id)->get(); // <-- INI

        return view('finance.shift.show', compact('shift', 'salesOrders', 'expenses', 'incomes'));
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

    /**
     * Dashboard khusus Finance (jika needed)
     * Bisa berisi summary keuangan
     */
    public function dashboard(): View
    {
        // Contoh: Total kas masuk bulan ini, dll
        $totalCash = Shift::where('status', 'closed')
            ->whereMonth('created_at', now()->month)
            ->sum('cash_total');
            
        $totalExpenses = Shift::where('status', 'closed')
            ->whereMonth('created_at', now()->month)
            ->sum('expense_total');

        return view('finance.shift.dashboard', compact('totalCash', 'totalExpenses'));
    }
}