<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lender extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'nic_number',
        'bill_number',
        'description',
        'amount',
        'date',
        'is_paid',
        'paid_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
        'date' => 'date',
    ];
}