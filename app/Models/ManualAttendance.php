<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualAttendance extends Model
{
    use HasFactory;

    protected $table = 'manual_attendances';

    protected $fillable = [
        'person_id',
        'status',
        'check_in_time',
        'check_out_time',
        'remarks',
        'marked_by',
        'entry_type'
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}