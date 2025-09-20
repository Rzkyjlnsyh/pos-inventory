<?php

namespace App\Http\Controllers\Admin;

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
use Carbon\Carbon;

class ShiftController extends Controller
{
    /**
     * Mulai shift baru (HANYA BUKA SHIFT)
     */
    public function start(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'initial_cash' => ['required', 'numeric', 'min:0'],
        ]);
    
        // CEK APAKAH SUDAH ADA SHIFT AKTIF DI TOKO INI
        // Asumsi: semua user bekerja di toko yang sama, atau tambahkan field store_id jika multi-toko
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
    
        return redirect()->route('admin.shift.dashboard')->with('success', 'Shift dimulai dengan kas awal Rp ' . number_format($validated['initial_cash'], 0, ',', '.'));
    }

    /**
     * Akhiri shift (HANYA TUTUP SHIFT)
     */
    public function end(Request $request): RedirectResponse
    {
        // Validasi dan logic PERSIS sama dengan Owner
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

        // Redirect ke HISTORY SHIFT ADMIN, bukan owner
        return redirect()->route('admin.shift.history')->with('success', 'Shift diakhiri. Kas diharapkan: Rp ' . number_format($expectedCash, 0, ',', '.') . ', Selisih: Rp ' . number_format($discrepancy, 0, ',', '.'));
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

        return back()->with('success', 'Pemasukan berhasil ditambahkan.');
    }

    /**
     * Tambahkan pengeluaran selama shift
     */
    public function expense(Request $request): RedirectResponse
    {
        // Validasi dan logic PERSIS sama dengan Owner
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

        return back()->with('success', 'Pengeluaran berhasil ditambahkan.');
    }

    /**
     * Dashboard Shift untuk Admin
     * HANYA menampilkan dashboard, tidak ada akses ke history detail atau export
     */
    public function dashboard(): View
    {
        $shift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();

        // SAMA DENGAN OWNER CONTROLLER, PASTIKAN ADA VARIABLE $pemasukanManual
        $cashLunas = 0;
        $cashDp = 0;
        $cashPelunasan = 0;
        $transferLunas = 0;
        $transferDp = 0;
        $transferPelunasan = 0;
        $pengeluaran = 0;
        $pemasukanManual = 0; // <-- INI
        $tunaiDiLaci = 0;
        $awalLaci = 0;
        $totalDiharapkan = 0;

        if ($shift) {
            $awalLaci = $shift->initial_cash;
            $pengeluaran = $shift->expense_total;
            $pemasukanManual = $shift->income_total; // <-- INI

            $salesOrders = SalesOrder::where('created_by', Auth::id())
                ->where('created_at', '>=', $shift->start_time)
                ->with(['payments' => function($query) {
                    $query->orderBy('paid_at');
                }])
                ->get();

            foreach ($salesOrders as $salesOrder) {
                $paymentCount = $salesOrder->payments->count();
                foreach ($salesOrder->payments as $index => $payment) {
                    $isFirstPayment = $index === 0;
                    $paidSoFar = 0;
                    for ($i = 0; $i <= $index; $i++) {
                        $paidSoFar += $salesOrder->payments[$i]->amount;
                    }
                    $isLunasPayment = $paidSoFar >= $salesOrder->grand_total;

                    if ($payment->method === 'cash') {
                        $amountToAdd = $payment->amount;
                        if ($isLunasPayment && $isFirstPayment) {
                            $cashLunas += $amountToAdd;
                        } else if ($isFirstPayment) {
                            $cashDp += $amountToAdd;
                        } else {
                            $cashPelunasan += $amountToAdd;
                        }
                    } else if ($payment->method === 'transfer') {
                        $amountToAdd = $payment->amount;
                        if ($isLunasPayment && $isFirstPayment) {
                            $transferLunas += $amountToAdd;
                        } else if ($isFirstPayment) {
                            $transferDp += $amountToAdd;
                        } else {
                            $transferPelunasan += $amountToAdd;
                        }
                    } else if ($payment->method === 'split') {
                        if ($isLunasPayment && $isFirstPayment) {
                            $cashLunas += $payment->cash_amount;
                            $transferLunas += $payment->transfer_amount;
                        } else if ($isFirstPayment) {
                            $cashDp += $payment->cash_amount;
                            $transferDp += $payment->transfer_amount;
                        } else {
                            $cashPelunasan += $payment->cash_amount;
                            $transferPelunasan += $payment->transfer_amount;
                        }
                    }
                }
            }

            $totalCashFromPayments = $cashLunas + $cashDp + $cashPelunasan;
            $tunaiDiLaci = $shift->initial_cash + $totalCashFromPayments + $pemasukanManual - $shift->expense_total;
            $totalDiharapkan = $shift->initial_cash + $totalCashFromPayments + $pemasukanManual - $shift->expense_total;
        }

        // Render view ADMIN, bukan owner
        return view('admin.shift.dashboard', compact(
            'shift',
            'cashLunas',
            'cashDp',
            'cashPelunasan',
            'transferLunas',
            'transferDp',
            'transferPelunasan',
            'pengeluaran',
            'pemasukanManual', // <-- INI
            'tunaiDiLaci',
            'awalLaci',
            'totalDiharapkan'
        ));
    }

    /**
     * Riwayat Shift untuk Admin
     * HANYA menampilkan list history, tidak bisa lihat detail atau export
     */
    public function history(): View
    {
        // Hanya tampilkan shift milik user (admin) ini saja
        $shifts = Shift::where('user_id', Auth::id())->orderBy('start_time', 'desc')->paginate(10);
        // Render view ADMIN, bukan owner
        return view('admin.shift.history', compact('shifts'));
    }
}