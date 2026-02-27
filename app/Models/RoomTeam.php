<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomTeam extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'notes',
        'check_in_date',
        'check_out_date',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class, 'team_id');
    }
}
