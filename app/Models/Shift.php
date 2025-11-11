<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'initial_cash',
        'cash_total',
        'expense_total',
        'final_cash',
        'discrepancy',
        'start_time',
        'end_time',
        'notes',
        'status', // Tambah status
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'initial_cash' => 'decimal:2',
        'cash_total' => 'decimal:2',
        'expense_total' => 'decimal:2',
        'final_cash' => 'decimal:2',
        'discrepancy' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

        // METHOD BARU: VALIDASI INTEGRITAS DATA
        public function validateCashIntegrity(): array
        {
            $realCashTotal = $this->calculateRealCashTotal();
            $realFinalCash = $this->calculateRealFinalCash();
            
            $cashTotalValid = abs($this->cash_total - $realCashTotal) < 0.01;
            $finalCashValid = abs($this->final_cash - $realFinalCash) < 0.01;
            
            return [
                'is_valid' => $cashTotalValid && $finalCashValid,
                'cash_total_diff' => $this->cash_total - $realCashTotal,
                'final_cash_diff' => $this->final_cash - $realFinalCash,
                'real_cash_total' => $realCashTotal,
                'real_final_cash' => $realFinalCash,
            ];
        }

            // METHOD BARU: HITUNG REAL CASH TOTAL
    public function calculateRealCashTotal(): float
    {
        $totalCashFromPayments = $this->payments->sum(function($payment) {
            if ($payment->method === 'cash') {
                return $payment->amount;
            } elseif ($payment->method === 'split') {
                return $payment->cash_amount;
            }
            return 0;
        });

        $totalIncome = $this->incomes->sum('amount');
        
        return $totalCashFromPayments + $totalIncome;
    }

        // METHOD BARU: HITUNG REAL FINAL CASH
        public function calculateRealFinalCash(): float
        {
            return $this->initial_cash + $this->calculateRealCashTotal() - $this->expense_total;
        }
    public function isFirstShift(): bool
{
    return Shift::whereNotNull('end_time')->count() === 0;
}
public function getPreviousShift(): ?Shift
{
    return Shift::whereNotNull('end_time')
                ->where('id', '<', $this->id)
                ->latest('end_time')
                ->first();
}
public function calculateFinalCash(): float
{
    return $this->initial_cash + $this->cash_total - $this->expense_total;
}

public function getExpectedCash(): float
{
    return $this->initial_cash + $this->cash_total - $this->expense_total;
}
}