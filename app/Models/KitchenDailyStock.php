<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenDailyStock extends Model
{
    protected $fillable = [
        'date',
        'item_id',
        'opening_balance',
        'received',
        'used',
        'expected_balance',
        'physical_count',
        'variance',
        'notes',
        'entered_by',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'opening_balance' => 'decimal:3',
        'received' => 'decimal:3',
        'used' => 'decimal:3',
        'expected_balance' => 'decimal:3',
        'physical_count' => 'decimal:3',
        'variance' => 'decimal:3',
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
