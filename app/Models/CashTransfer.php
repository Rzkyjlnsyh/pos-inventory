<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id',
        'amount', 
        'description',
        'type',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}