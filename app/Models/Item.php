<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = ['name', 'group_id'];

    public function group()
    {
        return $this->belongsTo(ProductGroup::class, 'group_id'); // group_id is the foreign key
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class, 'item_id'); // item_id is the foreign key
    }
}