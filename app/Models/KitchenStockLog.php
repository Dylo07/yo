<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitchenStockLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'action',
        'quantity',
        'previous_stock',
        'new_stock',
        'description',
        'user_id',
        'metadata'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'previous_stock' => 'decimal:2',
        'new_stock' => 'decimal:2',
        'metadata' => 'array'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
