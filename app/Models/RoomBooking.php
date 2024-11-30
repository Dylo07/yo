<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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

    protected $appends = ['stay_day_count', 'stay_status'];

    public function getStayDayCountAttribute()
    {
        if (!$this->guest_in_time) return 0;
        
        $checkDate = request('date', now()->format('Y-m-d'));
        $checkDate = Carbon::parse($checkDate);
        $daysDiff = $this->guest_in_time->diffInDays($checkDate) + 1;
        
        return $daysDiff;
    }

    public function getStayStatusAttribute()
    {
        $dayCount = $this->stay_day_count;
        
        if ($dayCount <= 1) {
            return 'Same Day';
        }
        
        $suffixes = [1 => 'st', 2 => 'nd', 3 => 'rd'];
        $suffix = isset($suffixes[$dayCount]) ? $suffixes[$dayCount] : 'th';
        
        return $dayCount . $suffix . ' day';
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}