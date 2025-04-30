<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySalesSummary extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'daily_sales_summaries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'date',
        'sale_id',
        'bill_number',
        'datetime',
        'rooms_amount',
        'swimming_pool_amount',
        'arrack_amount',
        'beer_amount',
        'other_amount',
        'service_charge',
        'description',
        'total_amount',
        'cash_payment',
        'card_payment',
        'bank_payment',
        'status',
        'verified',
        'is_manual',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'datetime' => 'datetime',
        'rooms_amount' => 'decimal:2',
        'swimming_pool_amount' => 'decimal:2',
        'arrack_amount' => 'decimal:2',
        'beer_amount' => 'decimal:2',
        'other_amount' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'cash_payment' => 'decimal:2',
        'card_payment' => 'decimal:2',
        'bank_payment' => 'decimal:2',
        'verified' => 'integer', // Changed from boolean to integer
        'is_manual' => 'integer', // Changed from boolean to integer
    ];

    /**
     * Get the user who created this record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the related sale record if this is linked to a system sale.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }
}