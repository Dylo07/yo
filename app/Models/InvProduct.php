<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvProduct extends Model
{
    protected $table = 'inv_products';
    protected $fillable = ['name', 'category_id'];

    public function category()
    {
        return $this->belongsTo(InvProductCategory::class, 'category_id');
    }

    public function inventories()
    {
        return $this->hasMany(InvInventory::class, 'product_id');
    }

    public function logs()
    {
        return $this->hasMany(InvInventoryLog::class, 'product_id');
    }
}
