<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        // properti existing
        'po_number',
        'order_date',
        'supplier_id',
        'subtotal',
        'discount_total',
        'grand_total',
        'status',
        'is_paid',
        'created_by',
        'approved_by',
        'approved_at',
        'received_at',
        'received_by',
        // tambahkan berikut
        'invoice_file',
        'payment_proof_file',
    ];

    protected $casts = [
        'order_date' => 'date',
        'approved_at' => 'datetime',
        'received_at' => 'datetime', // Tambahkan ini
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withDefault([
            'username' => 'Unknown User', // Default value jika user tidak ditemukan
        ]);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by')->withDefault([
            'username' => 'Unknown User', // Default value jika user tidak ditemukan
        ]);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by')->withDefault([
            'username' => 'Unknown User', // Default value jika user tidak ditemukan
        ]);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
    // app/Models/PurchaseOrder.php
public function cancel()
{
    if ($this->status === 'cancelled') {
        return; // sudah dicancel
    }
    
    // Kembalikan stok jika sudah diterima
    if ($this->status === 'received') {
        foreach ($this->items as $item) {
            $product = $item->product;
            $product->stock_qty -= $item->quantity;
            $product->save();
            
            // Catat stock movement
            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'purchase_cancel',
                'quantity' => -$item->quantity,
                'reference_id' => $this->id,
                'reference_type' => PurchaseOrder::class,
                'notes' => 'Pembelian dibatalkan: ' . $this->po_number
            ]);
        }
    }
    
    $this->status = 'cancelled';
    $this->save();
}
}