<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItemRecipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'item_id',
        'required_quantity',
        'preparation_notes',
        'is_optional'
    ];

    protected $casts = [
        'required_quantity' => 'decimal:3',
        'is_optional' => 'boolean'
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
