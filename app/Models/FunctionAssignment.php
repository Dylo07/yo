<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FunctionAssignment extends Model
{
    use HasFactory;

    protected $table = 'function_assignments';

    protected $fillable = [
        'booking_id',
        'person_id',
        'role',
        'notes',
        'assigned_by',
    ];

    /**
     * Get the booking for this assignment
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the person (staff member) for this assignment
     */
    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Get the user who assigned this
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
