<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisementPerformance extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'user_id',
        'type',
        'description',
        'amount'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'float',
    ];

    // === RELASI ===
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // === SCOPES ===
    public function scopeToday($query)
    {
        return $query->where('date', today());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    // === ACCESSORS ===
    public function getTypeLabelAttribute()
    {
        return [
            'chat' => 'Chat Masuk',
            'followup' => 'Follow Up',
            'closing' => 'Closing'
        ][$this->type] ?? $this->type;
    }

    // === DEFAULT DESCRIPTIONS ===
    public static function getDefaultDescriptions($type)
    {
        $descriptions = [
            'chat' => [
                'Tanya Harga',
                'Tanya Stock', 
                'Konsultasi Desain',
                'Tanya Lokasi',
                'Lainnya'
            ],
            'followup' => [
                'Harga Cocok',
                'Budget Kurang',
                'Tunggu Gajian',
                'Masih Compare',
                'Tunggu Spouse',
                'Lainnya'
            ],
            'closing' => [
                'Harga Deal',
                'Desain Sesuai', 
                'Pelayanan Memuaskan',
                'Kualitas Terjamin',
                'Lainnya'
            ]
        ];

        return $descriptions[$type] ?? [];
    }
}