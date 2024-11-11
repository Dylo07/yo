<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cost extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id', 'person_id', 'user_id', 'amount', 'cost_date',
    ];

    // Relationship with Group
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    // Relationship with Person
    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
