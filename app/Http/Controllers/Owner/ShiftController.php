<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\Payment;
use App\Models\SalesOrder;
use App\Models\Expense; // Asumsi tabel expenses sudah ada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ShiftHistoryExport;
use App\Exports\ShiftDetailExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ShiftController extends Controller
{
    public function start(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'initial_cash' => ['required', 'numeric', 'min:0'],
        ]);

        $existingShift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
        if ($existingShift) {
            return back()->withErrors(['error' => 'Anda sudah memiliki shift aktif. Akhiri shift sebelumnya terlebih dahulu.']);
        }

        Shift::create([
            'user_id' => Auth::id(),
            'initial_cash' => $validated['initial_cash'],
            'start_time' => now(),
            'status' => 'open',
        ]);

        // Ubah redirect: Kembali ke dashboard shift, bukan ke create sales
        return redirect()->route('owner.shift.dashboard')->with('success', 'Shift dimulai dengan kas awal Rp ' . number_format($validated['initial_cash'], 0, ',', '.'));
    }

    public function end(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'final_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $shift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
        if (!$shift) {
            return back()->withErrors(['error' => 'Anda tidak memiliki shift aktif. Mulai shift terlebih dahulu.']);
        }

        $expectedCash = $shift->initial_cash + $shift->cash_total - $shift->expense_total;
        $discrepancy = $validated['final_cash'] - $expectedCash;

        $shift->update([
            'final_cash' => $validated['final_cash'],
            'discrepancy' => $discrepancy,
            'end_time' => now(),
            'notes' => $validated['notes'] ?? null,
            'status' => 'closed',
        ]);

        return redirect()->route('owner.shift.history')->with('success', 'Shift diakhiri. Kas diharapkan: Rp ' . number_format($expectedCash, 0, ',', '.') . ', Selisih: Rp ' . number_format($discrepancy, 0, ',', '.'));
    }

    public function expense(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'expense_amount' => ['required', 'numeric', 'min:0.01'],
            'expense_description' => ['required', 'string', 'max:255'],
        ]);

        $shift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
        if (!$shift) {
            return back()->withErrors(['error' => 'Anda tidak memiliki shift aktif. Mulai shift terlebih dahulu.']);
        }

        // Simpan pengeluaran ke tabel expenses
        Expense::create([
            'shift_id' => $shift->id,
            'amount' => $validated['expense_amount'],
            'description' => $validated['expense_description'],
            'created_at' => now(),
        ]);

        $shift->increment('expense_total', $validated['expense_amount']);
        \Log::info('Pengeluaran ditambahkan: ' . $validated['expense_description'] . ' - Rp ' . number_format($validated['expense_amount'], 0, ',', '.'));

        return back()->with('success', 'Pengeluaran berhasil ditambahkan.');
    }

    public function dashboard(): View
    {
        $shift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
    
        // Inisialisasi variabel dengan nilai default
        $cashLunas = 0;
        $cashDp = 0;
        $cashPelunasan = 0;
        $transferLunas = 0;
        $transferDp = 0;
        $transferPelunasan = 0;
        $pengeluaran = 0;
        $tunaiDiLaci = 0;
        $awalLaci = 0;
        $totalDiharapkan = 0;
    
        if ($shift) {
            $awalLaci = $shift->initial_cash;
            $pengeluaran = $shift->expense_total;
            
            // Hitung tunai di laci
            $tunaiDiLaci = $shift->initial_cash + $shift->cash_total - $shift->expense_total;
            $totalDiharapkan = $shift->initial_cash + $shift->cash_total - $shift->expense_total;
    
            \Log::info('Shift cash_total: ' . $shift->cash_total);
            \Log::info('Shift initial_cash: ' . $shift->initial_cash);
            \Log::info('Shift expense_total: ' . $shift->expense_total);
    
            // Ambil semua sales order yang dibuat selama shift ini dengan payments
            $salesOrders = SalesOrder::where('created_by', Auth::id())
                ->where('created_at', '>=', $shift->start_time)
                ->with(['payments' => function($query) {
                    $query->orderBy('paid_at');
                }])
                ->get();
    
            \Log::info('Jumlah sales orders: ' . $salesOrders->count());
    
            foreach ($salesOrders as $salesOrder) {
                \Log::info('SO: ' . $salesOrder->so_number . ', Payments: ' . $salesOrder->payments->count());
                
                $paymentCount = $salesOrder->payments->count();
                
                foreach ($salesOrder->payments as $index => $payment) {
                    \Log::info('Payment ' . ($index + 1) . ': ' . $payment->method . ', Amount: ' . $payment->amount . ', Cash Amount: ' . $payment->cash_amount . ', Transfer Amount: ' . $payment->transfer_amount);
                    
                    $isFirstPayment = $index === 0;
                    
                    // Hitung total pembayaran sampai payment ini
                    $paidSoFar = 0;
                    for ($i = 0; $i <= $index; $i++) {
                        $paidSoFar += $salesOrder->payments[$i]->amount;
                    }
                    
                    $isLunasPayment = $paidSoFar >= $salesOrder->grand_total;
    
                    \Log::info('Paid so far: ' . $paidSoFar . ', Grand total: ' . $salesOrder->grand_total . ', Is lunas: ' . ($isLunasPayment ? 'Yes' : 'No'));
    
                    // PERBAIKAN DI SINI: Gunakan amount yang sesuai dengan method
                    if ($payment->method === 'cash') {
                        $amountToAdd = $payment->amount; // Untuk cash, gunakan amount
                        if ($isLunasPayment && $isFirstPayment) {
                            $cashLunas += $amountToAdd;
                            \Log::info('Added to cashLunas: ' . $amountToAdd);
                        } else if ($isFirstPayment) {
                            $cashDp += $amountToAdd;
                            \Log::info('Added to cashDp: ' . $amountToAdd);
                        } else {
                            $cashPelunasan += $amountToAdd;
                            \Log::info('Added to cashPelunasan: ' . $amountToAdd);
                        }
                    } else if ($payment->method === 'transfer') {
                        $amountToAdd = $payment->amount; // Untuk transfer, gunakan amount
                        if ($isLunasPayment && $isFirstPayment) {
                            $transferLunas += $amountToAdd;
                            \Log::info('Added to transferLunas: ' . $amountToAdd);
                        } else if ($isFirstPayment) {
                            $transferDp += $amountToAdd;
                            \Log::info('Added to transferDp: ' . $amountToAdd);
                        } else {
                            $transferPelunasan += $amountToAdd;
                            \Log::info('Added to transferPelunasan: ' . $amountToAdd);
                        }
                    } else if ($payment->method === 'split') {
                        // Untuk split, pisahkan cash dan transfer
                        if ($isLunasPayment && $isFirstPayment) {
                            $cashLunas += $payment->cash_amount;
                            $transferLunas += $payment->transfer_amount;
                            \Log::info('Added split to cashLunas: ' . $payment->cash_amount . ', transferLunas: ' . $payment->transfer_amount);
                        } else if ($isFirstPayment) {
                            $cashDp += $payment->cash_amount;
                            $transferDp += $payment->transfer_amount;
                            \Log::info('Added split to cashDp: ' . $payment->cash_amount . ', transferDp: ' . $payment->transfer_amount);
                        } else {
                            $cashPelunasan += $payment->cash_amount;
                            $transferPelunasan += $payment->transfer_amount;
                            \Log::info('Added split to cashPelunasan: ' . $payment->cash_amount . ', transferPelunasan: ' . $payment->transfer_amount);
                        }
                    }
                }
            }
    
            \Log::info('Final totals - CashLunas: ' . $cashLunas . ', CashDp: ' . $cashDp . ', CashPelunasan: ' . $cashPelunasan);
            \Log::info('Final totals - TransferLunas: ' . $transferLunas . ', TransferDp: . ' . $transferDp . ', TransferPelunasan: ' . $transferPelunasan);
        }
    
        return view('owner.shift.dashboard', compact(
            'shift', 
            'cashLunas', 
            'cashDp', 
            'cashPelunasan', 
            'transferLunas', 
            'transferDp', 
            'transferPelunasan', 
            'pengeluaran',
            'tunaiDiLaci',
            'awalLaci',
            'totalDiharapkan'
        ));
    }

    public function history(): View
    {
        $shifts = Shift::with('user')->orderBy('start_time', 'desc')->paginate(10);
        return view('owner.shift.history', compact('shifts'));
    }

    public function show(Shift $shift): View
    {
        $salesOrders = SalesOrder::where('created_by', $shift->user_id)
            ->whereBetween('created_at', [$shift->start_time, $shift->end_time ?? now()])
            ->with('payments')
            ->get();

        $expenses = Expense::where('shift_id', $shift->id)->get();

        return view('owner.shift.show', compact('shift', 'salesOrders', 'expenses'));
    }

    public function export()
    {
        return Excel::download(new ShiftHistoryExport, 'shift_history_' . date('Ymd_His') . '.xlsx');
    }

    public function exportDetail(Shift $shift)
    {
        return Excel::download(new ShiftDetailExport($shift), 'shift_detail_' . $shift->id . '_' . date('Ymd_His') . '.xlsx');
    }

    public function exportDetailPdf(Shift $shift)
    {
        $salesOrders = SalesOrder::where('created_by', $shift->user_id)
            ->whereBetween('created_at', [$shift->start_time, $shift->end_time ?? now()])
            ->with('payments')
            ->get();

        $expenses = Expense::where('shift_id', $shift->id)->get();

        $pdf = Pdf::loadView('owner.shift.detail_pdf', compact('shift', 'salesOrders', 'expenses'));
        return $pdf->download('shift_detail_' . $shift->id . '_' . date('Ymd_His') . '.pdf');
    }
}