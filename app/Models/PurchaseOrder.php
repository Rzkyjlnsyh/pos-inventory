<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    // Status untuk approval workflow
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_CANCELLED = 'cancelled';
    
    // Status untuk production workflow - Kain
    const STATUS_PAYMENT = 'payment';
    const STATUS_KAIN_DITERIMA = 'kain_diterima';
    const STATUS_PRINTING = 'printing';
    const STATUS_JAHIT = 'jahit';
    const STATUS_SELESAI = 'selesai';
    
    // Purchase Types
    const TYPE_KAIN = 'kain';
    const TYPE_PRODUK_JADI = 'produk_jadi';

    protected $fillable = [
        // existing properties
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
        'invoice_file',
        'payment_proof_file',
        
        // new properties
        'purchase_type', // 'kain' atau 'produk_jadi'
        'payment_at',
        'payment_by',
        'kain_diterima_at',
        'kain_diterima_by',
        'printing_at',
        'printing_by',
        'jahit_at',
        'jahit_by',
        'selesai_at',
        'selesai_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'approved_at' => 'datetime',
        'received_at' => 'datetime',
        'payment_at' => 'datetime',
        'kain_diterima_at' => 'datetime',
        'printing_at' => 'datetime',
        'jahit_at' => 'datetime',
        'selesai_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withDefault([
            'name' => 'Unknown User',
        ]);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by')->withDefault([
            'name' => 'Unknown User',
        ]);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by')->withDefault([
            'name' => 'Unknown User',
        ]);
    }

    // New relationships for tracking users in production workflow
    public function paymentProcessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_by')->withDefault([
            'name' => 'Unknown User',
        ]);
    }

    public function kainReceiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kain_diterima_by')->withDefault([
            'name' => 'Unknown User',
        ]);
    }

    public function printer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printing_by')->withDefault([
            'name' => 'Unknown User',
        ]);
    }

    public function tailor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'jahit_by')->withDefault([
            'name' => 'Unknown User',
        ]);
    }

    public function finisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'selesai_by')->withDefault([
            'name' => 'Unknown User',
        ]);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    // Helper methods untuk workflow
    public function isKainType(): bool
    {
        return $this->purchase_type === self::TYPE_KAIN;
    }

    public function isProdukJadiType(): bool
    {
        return $this->purchase_type === self::TYPE_PRODUK_JADI;
    }

    public function getNextAvailableStatuses(): array
    {
        $currentStatus = $this->status;
        
        if ($this->isKainType()) {
            return match($currentStatus) {
                self::STATUS_DRAFT => [self::STATUS_PENDING],
                self::STATUS_PENDING => [self::STATUS_APPROVED, self::STATUS_CANCELLED],
                self::STATUS_APPROVED => [self::STATUS_PAYMENT],
                self::STATUS_PAYMENT => [self::STATUS_KAIN_DITERIMA],
                self::STATUS_KAIN_DITERIMA => [self::STATUS_PRINTING],
                self::STATUS_PRINTING => [self::STATUS_JAHIT],
                self::STATUS_JAHIT => [self::STATUS_SELESAI],
                default => []
            };
        }
        
        // Produk Jadi workflow
        return match($currentStatus) {
            self::STATUS_DRAFT => [self::STATUS_PENDING],
            self::STATUS_PENDING => [self::STATUS_APPROVED, self::STATUS_CANCELLED],
            self::STATUS_APPROVED => [self::STATUS_PAYMENT],
            self::STATUS_PAYMENT => [self::STATUS_SELESAI],
            default => []
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING => 'Pending Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_PAYMENT => 'Payment',
            self::STATUS_KAIN_DITERIMA => 'Kain Diterima',
            self::STATUS_PRINTING => 'Printing',
            self::STATUS_JAHIT => 'Jahit',
            self::STATUS_SELESAI => 'Selesai',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst($this->status)
        };
    }

    public function getTypeLabel(): string
    {
        return match($this->purchase_type) {
            self::TYPE_KAIN => 'Pembelian Kain',
            self::TYPE_PRODUK_JADI => 'Pembelian Produk Jadi',
            default => 'Unknown Type'
        };
    }

    public function cancel()
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return; // sudah dicancel
        }

        // Kembalikan stok jika sudah selesai (baik kain maupun produk jadi)
        if ($this->status === self::STATUS_SELESAI) {
            foreach ($this->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->stock_qty -= $item->qty;
                    $product->save();

                    // Catat stock movement
                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'purchase_cancel',
                        'quantity' => -$item->qty,
                        'reference_id' => $this->id,
                        'reference_type' => PurchaseOrder::class,
                        'notes' => 'Pembelian dibatalkan: ' . $this->po_number
                    ]);
                }
            }
        }

        $this->status = self::STATUS_CANCELLED;
        $this->save();
    }

    // Method untuk update status dengan tracking user
    public function updateStatus(string $newStatus, int $userId): bool
    {
        $availableStatuses = $this->getNextAvailableStatuses();
        
        if (!in_array($newStatus, $availableStatuses)) {
            return false;
        }

        $this->status = $newStatus;
        
        // Set timestamp dan user berdasarkan status
        match($newStatus) {
            self::STATUS_APPROVED => [
                $this->approved_by = $userId,
                $this->approved_at = now()
            ],
            self::STATUS_PAYMENT => [
                $this->payment_by = $userId,
                $this->payment_at = now()
            ],
            self::STATUS_KAIN_DITERIMA => [
                $this->kain_diterima_by = $userId,
                $this->kain_diterima_at = now()
            ],
            self::STATUS_PRINTING => [
                $this->printing_by = $userId,
                $this->printing_at = now()
            ],
            self::STATUS_JAHIT => [
                $this->jahit_by = $userId,
                $this->jahit_at = now()
            ],
            self::STATUS_SELESAI => [
                $this->selesai_by = $userId,
                $this->selesai_at = now()
            ],
            default => null
        };

        return $this->save();
    }
}