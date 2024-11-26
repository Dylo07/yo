<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; // Correct Carbon import

class Cost extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'person_id',
        'user_id',
        'amount',
        'cost_date',
    ];

    protected $casts = [
        'cost_date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Ensure cost_date is always a Carbon instance
    public function getCostDateAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper method to format amount
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    // Helper method to format date
    public function getFormattedDateAttribute()
    {
        return $this->cost_date ? $this->cost_date->format('M d, Y') : 'N/A';
    }
}