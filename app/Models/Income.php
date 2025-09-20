<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id',
        'amount',
        'description'
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}