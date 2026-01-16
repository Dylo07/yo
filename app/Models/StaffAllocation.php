<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffAllocation extends Model
{
    use HasFactory;

    protected $table = 'staff_allocations';

    protected $fillable = [
        'person_id',
        'section_id',
        'section_name',
        'allocation_date',
        'assigned_by',
    ];

    protected $casts = [
        'allocation_date' => 'date',
    ];

    /**
     * Get the person (staff member) for this allocation
     */
    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Get the user who assigned this allocation
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
