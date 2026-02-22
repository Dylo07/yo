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
        'current_stock',
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
        return $this->current_stock <= $this->minimum_stock;
    }
}
