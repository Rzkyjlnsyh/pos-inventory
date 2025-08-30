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
'so_number', 'order_date', 'customer_id', 'subtotal',
'discount_total', 'grand_total', 'status', 'payment_method',
'payment_status', 'created_by', 'approved_by', 'approved_at',
'completed_at', 'dtf_confirmation'
];

// === RELASI ===
public function payments()
{
return $this->hasMany(Payment::class);
}

public function items(): HasMany
{
return $this->hasMany(SalesOrderItem::class);
}

public function customer(): BelongsTo
{
return $this->belongsTo(Customer::class);
}

public function creator(): BelongsTo
{
return $this->belongsTo(User::class, 'created_by');
}

public function approver(): BelongsTo
{
return $this->belongsTo(User::class, 'approved_by');
}

// === ACCESSOR ===
public function getPaidTotalAttribute(): float
{
return (float) $this->payments()->sum('amount');
}

public function getRemainingAmountAttribute(): float
{
return (float) max(0, $this->grand_total - $this->paid_total);
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
return $this->status !== 'selesai';
}
}