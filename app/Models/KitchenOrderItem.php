<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitchenOrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'kitchen_order_id',
        'menu_id',
        'menu_name',
        'qty',
        'notes',
        'status',
        'prep_time',
    ];

    /**
     * Get the kitchen order that owns the item.
     */
    public function order()
    {
        return $this->belongsTo(KitchenOrder::class, 'kitchen_order_id');
    }
    
    /**
     * Calculate progress percentage for order item
     */
    public function getProgressPercentageAttribute()
    {
        switch($this->status) {
            case 'ready':
                return 100;
            case 'cooking':
                return 50;
            default:
                return 0;
        }
    }
    
    /**
     * Get the progress bar color based on status
     */
    public function getProgressColorAttribute()
    {
        switch($this->status) {
            case 'ready':
                return 'bg-green-500';
            case 'cooking':
                return 'bg-yellow-500';
            default:
                return 'bg-gray-400';
        }
    }
}