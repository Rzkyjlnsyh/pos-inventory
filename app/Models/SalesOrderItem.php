<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderItem extends Model
{
use HasFactory;

protected $fillable = [
'sales_order_id', 'product_id', 'product_name', 'sku',
'sale_price', 'qty', 'discount', 'product_type', 'line_total'
];

public function salesOrder(): BelongsTo
{
return $this->belongsTo(SalesOrder::class);
}

public function product(): BelongsTo
{
return $this->belongsTo(Product::class);
}
}