<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrder extends Model
{
use HasFactory;

protected $fillable = [
    'so_number',
    'order_type',
    'order_date',
    'customer_id',
    'subtotal',
    'discount_total',
    'grand_total',
    'status',
    'payment_method',
    'payment_status',
    'created_by',
    'approved_by',
    'approved_at',
    'completed_at',
];
protected $casts = [
    'subtotal' => 'decimal:2',
    'discount_total' => 'decimal:2',
    'grand_total' => 'decimal:2',
    'order_date' => 'date',
    'approved_at' => 'datetime',
    'completed_at' => 'datetime',
];

// === RELASI ===
public function payments()
{
    return $this->hasMany(Payment::class)->orderBy('paid_at'); // Tambahkan orderBy
}

public function customer()
{
    return $this->belongsTo(Customer::class);
}

public function items()
{
    return $this->hasMany(SalesOrderItem::class);
}

public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}

public function approver()
{
    return $this->belongsTo(User::class, 'approved_by');
}

// === ACCESSOR ===
public function getPaidTotalAttribute()
{
    return $this->payments->sum('amount');
}

public function getRemainingAmountAttribute()
{
    return $this->grand_total - $this->paid_total;
}

// === Validasi Status ===
public static function allowedStatuses(): array
{
return ['draft', 'pending', 'di proses', 'selesai'];
}

public function isValidTransition(string $newStatus): bool
{
$currentStatus = $this->status;
$transitions = [
'draft' => ['pending'],
'pending' => ['di proses'],
'di proses' => ['selesai'],
];
return in_array($newStatus, $transitions[$currentStatus] ?? []);
}

public function isEditable(): bool
{
    return in_array($this->status, ['pending']);
}
}