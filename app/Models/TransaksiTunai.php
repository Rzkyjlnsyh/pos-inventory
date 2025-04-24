<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiTunai extends Model
{
    use HasFactory;

    protected $fillable = [
        'subtotal',
        'total_cost_price',
        'name_user',
        'payment_method',
        'timestamp'
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'subtotal' => 'decimal:2',
        'total_cost_price' => 'decimal:2'
    ];
    
}
