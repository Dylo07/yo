<?php

// BookingPayment.php model
namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class BookingPayment extends Model
{
    protected $fillable = [
        'booking_id',
        'amount',
        'bill_number',
        'payment_date',
        'payment_method',
        'is_verified',
    'verified_at',
    'verified_by'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'is_verified' => 'boolean'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function verifier()
{
    return $this->belongsTo(User::class, 'verified_by');
}
}