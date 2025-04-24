<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiQris extends Model
{
    use HasFactory;

    protected $fillable = [
        'subtotal',
        'total_cost_price',
        'name_user',
        'payment_method',
        'timestamp',
    ];
    
}
