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

    // Hapus ini jika migration payments tidak punya shift_id
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