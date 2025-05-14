<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitchenOrder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'table_id',
        'table_name',
        'server',
        'guests',
        'source',
        'time', 
        'estimated_complete',
        'status',
        'priority',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the items for the kitchen order.
     */
    public function items()
    {
        return $this->hasMany(KitchenOrderItem::class);
    }
    
    /**
     * Get the order status badge color
     */
    public function getStatusBadgeColorAttribute()
    {
        switch($this->status) {
            case 'NEW':
                return 'bg-red-500';
            case 'COOKING':
                return 'bg-yellow-500';
            case 'READY':
                return 'bg-green-500';
            default:
                return 'bg-gray-500';
        }
    }
    
    /**
     * Get the order card background based on status and priority
     */
    public function getCardBackgroundAttribute()
    {
        if ($this->status === 'NEW') {
            return $this->priority === 'high' ? 'bg-red-100 border-red-500' : 'bg-red-50 border-red-400';
        } elseif ($this->status === 'COOKING') {
            return $this->priority === 'high' ? 'bg-yellow-100 border-yellow-500' : 'bg-yellow-50 border-yellow-400';
        } else {
            return 'bg-green-100 border-green-500';
        }
    }
}