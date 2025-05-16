<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InStock extends Model
{
    protected $table = 'in_stocks';
    
    protected $fillable = [
        'menu_id', 'stock', 'user_id' , 'sale_id'
    ];

    /**
     * Get the menu that owns the stock entry.
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    /**
     * Get the user that created the stock entry.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}