<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomBooking extends Model
{
    protected $fillable = [
        'room_id',
        'guest_in_time',
        'guest_out_time',
    ];

    protected $casts = [
        'guest_in_time' => 'datetime',
        'guest_out_time' => 'datetime'
    ];

    // Computed property for multiple day stays
    protected $appends = ['spans_multiple_days'];

    public function getSpansMultipleDaysAttribute()
    {
        if (!$this->guest_out_time) return false;
        return $this->guest_in_time->format('Y-m-d') !== $this->guest_out_time->format('Y-m-d');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}