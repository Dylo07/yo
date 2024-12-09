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
        'fingerprint_data',
        'device_id',
        'verification_status'
    ];

    protected $dates = [
        'date',
        'check_in',
        'check_out'
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}