<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cost extends Model
{
    use HasFactory;

    protected $fillable = ['group_id', 'person_id', 'amount', 'cost_date'];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
    
    public function person()
    {
        return $this->belongsTo(Person::class);
    }
    
}
