<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductGroup extends Model
{
    protected $fillable = ['name'];

    public function items()
    {
        return $this->hasMany(Item::class, 'group_id'); // group_id is the foreign key
    }
}