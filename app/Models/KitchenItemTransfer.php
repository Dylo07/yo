<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitchenItemTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'kitchen_item_id',
        'transfer_type',
        'quantity',
        'source',
        'destination',
        'status',
        'notes',
        'requested_by',
        'approved_by',
        'requested_at',
        'approved_at',
        'completed_at'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    public function kitchenItem()
    {
        return $this->belongsTo(KitchenItem::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}