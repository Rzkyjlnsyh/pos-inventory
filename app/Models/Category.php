<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'description',
        'is_active',
    ];

    protected $withCount = ['products']; // Tambahkan ini

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}