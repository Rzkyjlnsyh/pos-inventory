<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpname extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_number',
        'date',
        'notes',
        'status',
        'user_id',
        'verified_by',
        'approved_by',
        'approved_at',
    ];
    
    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected $appends = ['creator_label', 'approver_label'];

    public function items(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
    
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getCreatorLabelAttribute(): string
    {
        if (!$this->creator) return '-';
        return $this->creator->name ?? $this->creator->email;
    }

    public function getApproverLabelAttribute(): string
    {
        if (!$this->approver) return '-';
        return $this->approver->name ?? $this->approver->email;
    }
    public function purchaseOrder()
{
    return $this->belongsTo(PurchaseOrder::class);
}
public function product()
{
    return $this->belongsTo(Product::class);
}
}