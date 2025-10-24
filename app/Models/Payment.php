<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_order_id',
        'method',
        'status',
        'category', // Tambahkan ini
        'amount',
        'cash_amount',
        'transfer_amount',
        'paid_at',
        'reference',
        'reference_number',
        'proof_path',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'cash_amount' => 'decimal:2',
        'transfer_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}