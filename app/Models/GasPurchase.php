<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GasPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'gas_cylinder_id',
        'filled_received',
        'empty_returned',
        'price_per_unit',
        'total_amount',
        'dealer_name',
        'invoice_number',
        'purchase_date',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'purchase_date' => 'date',
    ];

    public function gasCylinder()
    {
        return $this->belongsTo(GasCylinder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
