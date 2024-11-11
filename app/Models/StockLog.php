<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    protected $fillable = ['item_id', 'user_id', 'action', 'quantity', 'description'];

    protected $casts = [
        'quantity' => 'float', // Ensure quantity is treated as a float
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
