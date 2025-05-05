<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ManualAttendance extends Model
{
    use HasFactory;

    protected $table = 'manual_attendances';

    protected $fillable = [
       'person_id',
       'status',
       'attendance_date',
       'remarks',
       'marked_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    /**
     * Ensure the attendance_date attribute gets properly cast to a date
     * without any time component to avoid time zone issues
     */
    public function setAttendanceDateAttribute($value)
    {
        $this->attributes['attendance_date'] = $value instanceof Carbon 
            ? $value->startOfDay()->format('Y-m-d') 
            : Carbon::parse($value)->startOfDay()->format('Y-m-d');
    }

    /**
     * Get the attendance_date attribute as a Carbon instance
     */
    public function getAttendanceDateAttribute($value)
    {
        return Carbon::parse($value);
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}