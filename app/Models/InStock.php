<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InStock extends Model
{
    protected $table = 'in_stocks';
    
    protected $fillable = [
        'menu_id', 'stock', 'user_id' , 'sale_id', 'notes'
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

    /**
     * Get the sale associated with this stock entry.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    /**
     * Get the daily sales summary associated with this stock entry.
     * Description is stored in daily_sales_summaries, not sales table.
     */
    public function dailySalesSummary()
    {
        return $this->belongsTo(DailySalesSummary::class, 'sale_id', 'bill_number');
    }
}