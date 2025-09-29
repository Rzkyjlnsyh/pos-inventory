<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrderLog extends Model
{
    protected $fillable = [
        'sales_order_id',
        'user_id',
        'action',
        'description',
        'created_at',
    ];

    public $timestamps = false; // Nonaktifkan updated_at

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}