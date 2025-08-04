<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'staff_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'raw_data'
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime:H:i',
        'check_out' => 'datetime:H:i',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $dates = [
        'date',
        'created_at',
        'updated_at'
    ];

    // Define relationship with Staff model
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    // Accessor to get formatted check in time
    public function getCheckInTimeAttribute()
    {
        return $this->check_in ? $this->check_in->format('H:i') : null;
    }

    // Accessor to get formatted check out time
    public function getCheckOutTimeAttribute()
    {
        return $this->check_out ? $this->check_out->format('H:i') : null;
    }
    
    // Method to get all punch times from raw_data
    public function getPunchTimesAttribute()
    {
        if (empty($this->raw_data)) {
            return [];
        }
        
        return array_filter(explode(' ', trim($this->raw_data)));
    }
}