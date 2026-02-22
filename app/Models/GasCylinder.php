<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GasCylinder extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'weight_kg',
        'price',
        'filled_stock',
        'empty_stock',
        'minimum_stock',
        'is_active',
    ];

    protected $casts = [
        'weight_kg' => 'decimal:2',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function purchases()
    {
        return $this->hasMany(GasPurchase::class);
    }

    public function issues()
    {
        return $this->hasMany(GasIssue::class);
    }

    public function isLowStock()
    {
        return $this->filled_stock <= $this->minimum_stock;
    }

    public function getTotalStockAttribute()
    {
        return $this->filled_stock + $this->empty_stock;
    }
}
