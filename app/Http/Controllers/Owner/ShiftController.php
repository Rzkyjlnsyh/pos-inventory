<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\Payment;
use App\Models\SalesOrder;
use App\Models\Expense;
use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ShiftHistoryExport;
use App\Exports\ShiftDetailExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ShiftController extends Controller
{
    public function start(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'initial_cash' => ['required', 'numeric', 'min:0'],
        ]);
    
        // CEK APAKAH SUDAH ADA SHIFT AKTIF DI TOKO INI
        $existingShift = Shift::whereNull('end_time')->first();
        
        if ($existingShift) {
            return back()->withErrors([
                'error' => 'Tidak bisa mulai shift. Shift aktif sedang berjalan oleh ' . 
                          $existingShift->user->name . 
                          ' sejak ' . $existingShift->start_time->format('H:i')
            ]);
        }
    
        // CEK APAKAH USER INI SUDAH PUNYA SHIFT AKTIF
        $userActiveShift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
        if ($userActiveShift) {
            return back()->withErrors(['error' => 'Anda sudah memiliki shift aktif. Akhiri shift sebelumnya terlebih dahulu.']);
        }
    
        Shift::create([
            'user_id' => Auth::id(),
            'initial_cash' => $validated['initial_cash'],
            'start_time' => now(),
            'status' => 'open',
        ]);
    
        return redirect()->route('owner.shift.dashboard')->with('success', 'Shift dimulai dengan kas awal Rp ' . number_format($validated['initial_cash'], 0, ',', '.'));
    }

    public function end(Request $request): Response|RedirectResponse
    {
        $validated = $request->validate([
            'final_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'print_summary' => ['nullable', 'boolean'],
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

        if ($request->has('print_summary')) {
            // Generate dan simpan PDF ke storage
            $pdfPath = $this->printShiftSummary($shift->id);
            // Simpan path PDF di session
            session()->flash('pdf_download', $pdfPath);
        }
    
        return redirect()->route('owner.shift.history')->with('success', 'Shift diakhiri. Kas diharapkan: Rp ' . number_format($expectedCash, 0, ',', '.') . ', Selisih: Rp ' . number_format($discrepancy, 0, ',', '.'));
    }

    public function printShiftSummary($id)
    {
        $shift = Shift::with('user')->findOrFail($id);
        $incomes = Income::where('shift_id', $id)->get();
        $expenses = Expense::where('shift_id', $id)->get();
        $salesOrders = SalesOrder::whereHas('payments', function ($query) use ($shift) {
            $query->where('created_by', $shift->user_id)
                  ->where('created_at', '>=', $shift->start_time)
                  ->where('created_at', '<=', $shift->end_time ?? now());
        })->with(['customer', 'payments'])->get();
    
        $pdf = PDF::loadView('owner.shift.closing_summary', compact('shift', 'incomes', 'expenses', 'salesOrders'));
        $pdfPath = 'pdfs/closing_summary_' . $shift->id . '.pdf';
        Storage::put($pdfPath, $pdf->output());
        return $pdfPath;
    }

    public function income(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'income_amount' => ['required', 'numeric', 'min:0.01'],
            'income_description' => ['required', 'string', 'max:255'],
        ]);

        $shift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
        if (!$shift) {
            return back()->withErrors(['error' => 'Anda tidak memiliki shift aktif. Mulai shift terlebih dahulu.']);
        }

        Income::create([
            'shift_id' => $shift->id,
            'amount' => $validated['income_amount'],
            'description' => $validated['income_description'],
        ]);

        $shift->increment('income_total', $validated['income_amount']);
        $shift->increment('cash_total', $validated['income_amount']);

        \Log::info('Pemasukan ditambahkan: ' . $validated['income_description'] . ' - Rp ' . number_format($validated['income_amount'], 0, ',', '.'));

        return back()->with('success', 'Pemasukan berhasil ditambahkan.');
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
        $pemasukanManual = 0;
        $tunaiDiLaci = 0;
        $awalLaci = 0;
        $totalDiharapkan = 0;
    
        if ($shift) {
            $awalLaci = $shift->initial_cash;
            $pengeluaran = $shift->expense_total;
            $pemasukanManual = $shift->income_total;
        
            // Ambil semua pembayaran yang dibuat selama shift ini, berdasarkan Payment.created_at
            $payments = Payment::where('created_by', Auth::id())
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
        
            // Hitung total cash dari pembayaran + pemasukan manual
            $totalCashFromPayments = $cashLunas + $cashDp + $cashPelunasan;
            $totalCashFromAllSources = $totalCashFromPayments + $pemasukanManual;
            
            // Hitung tunai di laci
            $tunaiDiLaci = $shift->initial_cash + $totalCashFromAllSources - $shift->expense_total;
            $totalDiharapkan = $shift->initial_cash + $totalCashFromAllSources - $shift->expense_total;
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
            'pemasukanManual',
            'tunaiDiLaci',
            'awalLaci',
            'totalDiharapkan'
        ));
    }

    public function history()
    {
        $shifts = Shift::with('user')->latest()->paginate(10);
        collect($shifts->items())->map(function ($shift) {
            $payments = Payment::where('created_by', $shift->user_id)
                ->where('created_at', '>=', $shift->start_time)
                ->where('created_at', '<=', $shift->end_time ?? now())
                ->with('salesOrder')
                ->get();
            
            $shift->cashLunas = 0;
            $shift->cashDp = 0;
            $shift->cashPelunasan = 0;
            $shift->transferTotal = 0;
        
            foreach ($payments as $payment) {
                $so = $payment->salesOrder;
                $isLunasSekaliBayar = ($payment->category === 'pelunasan' && $so->payments->count() === 1);
                if ($payment->method === 'cash') {
                    if ($isLunasSekaliBayar) {
                        $shift->cashLunas += $payment->amount;
                    } elseif ($payment->category === 'dp') {
                        $shift->cashDp += $payment->amount;
                    } else {
                        $shift->cashPelunasan += $payment->amount;
                    }
                } elseif ($payment->method === 'transfer') {
                    if ($isLunasSekaliBayar) {
                        $shift->transferTotal += $payment->amount;
                    } elseif ($payment->category === 'dp') {
                        $shift->transferTotal += $payment->amount;
                    } else {
                        $shift->transferTotal += $payment->amount;
                    }
                } elseif ($payment->method === 'split') {
                    if ($isLunasSekaliBayar) {
                        $shift->cashLunas += $payment->cash_amount;
                        $shift->transferTotal += $payment->transfer_amount;
                    } elseif ($payment->category === 'dp') {
                        $shift->cashDp += $payment->cash_amount;
                        $shift->transferTotal += $payment->transfer_amount;
                    } else {
                        $shift->cashPelunasan += $payment->cash_amount;
                        $shift->transferTotal += $payment->transfer_amount;
                    }
                }
            }
        
            return $shift;
        });
        
        return view('owner.shift.history', compact('shifts'));
    }

    public function show(Shift $shift)
    {
        $incomes = Income::where('shift_id', $shift->id)->get();
        $expenses = Expense::where('shift_id', $shift->id)->get();
    
        // Query sales orders yang punya pembayaran di shift ini
        $salesOrders = SalesOrder::whereHas('payments', function ($query) use ($shift) {
            $query->where('created_by', $shift->user_id)
                  ->where('created_at', '>=', $shift->start_time)
                  ->where('created_at', '<=', $shift->end_time ?? now());
        })->with(['customer', 'payments'])->get();
    
        // Hitung pembayaran
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
    
        return view('owner.shift.show', compact('shift', 'incomes', 'expenses', 'salesOrders', 'cashLunas', 'cashDp', 'cashPelunasan', 'transferLunas', 'transferDp', 'transferPelunasan'));
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
        // Ambil SO yang memiliki pembayaran dalam rentang shift
        $salesOrders = SalesOrder::whereHas('payments', function ($query) use ($shift) {
            $query->where('created_by', $shift->user_id)
                  ->where('created_at', '>=', $shift->start_time)
                  ->where('created_at', '<=', $shift->end_time ?? now());
        })->with(['payments' => function ($query) use ($shift) {
            $query->where('created_at', '>=', $shift->start_time)
                  ->where('created_at', '<=', $shift->end_time ?? now());
        }])->get();
    
        $expenses = Expense::where('shift_id', $shift->id)->get();
        $incomes = Income::where('shift_id', $shift->id)->get();

        $pdf = Pdf::loadView('owner.shift.detail_pdf', compact('shift', 'salesOrders', 'expenses', 'incomes'));
        return $pdf->download('shift_detail_' . $shift->id . '_' . date('Ymd_His') . '.pdf');
    }

    public function exportPdf()
    {
        $shifts = Shift::with('user')->orderBy('start_time', 'desc')->get();
        
        $pdf = Pdf::loadView('owner.shift.pdf', compact('shifts'));
        return $pdf->download('laporan_shift_' . date('Ymd_His') . '.pdf');
    }
}