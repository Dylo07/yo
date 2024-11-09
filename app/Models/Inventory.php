<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory'; // Explicitly set the table name if it is `inventory` and not `inventories`
    protected $fillable = ['item_id', 'stock_date', 'stock_level'];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id'); // item_id is the foreign key
    }
}