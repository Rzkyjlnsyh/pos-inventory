<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class PurchaseReturn extends Model
{
    use HasFactory;

    const TYPE_DEFECT = 'defect';      // Stok berkurang
    const TYPE_EXTRA = 'extra';        // Stok bertambah
    protected $fillable = [
        'return_number',
        'purchase_order_id',
        'supplier_id',
        'return_date',
        'reason',
        'notes',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'restock',
        'return_type',
        'created_by'
    ];

    protected $casts = [
        'return_date' => 'date', // TAMBAHKAN INI
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'restock' => 'boolean',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    // Accessor untuk format tanggal
    public function getFormattedReturnDateAttribute()
    {
        return Carbon::parse($this->return_date)->format('d M Y');
    }
    public function product()
{
    return $this->belongsTo(Product::class);
}
public function getReturnTypeLabelAttribute()
{
    return $this->return_type === self::TYPE_EXTRA ? 'Produksi Lebih' : 'Return Rusak';
}
}