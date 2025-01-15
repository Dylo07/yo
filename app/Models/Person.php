<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    // Specify the table name if it does not follow Laravel's convention
    protected $table = 'persons';

    // Define fillable fields to allow mass assignment
    protected $fillable = ['name', 'type'];

    // Set default values for attributes
    protected $attributes = [
        'type' => 'individual', // Default type is 'individual'
    ];

    // Define relationships
    public function costs()
    {
        return $this->hasMany(Cost::class);
    }

    // Add this new relationship
    public function staffCode()
    {
        return $this->hasOne(StaffCode::class, 'person_id');
    }

    // Add relationship to manual attendances
    public function manualAttendances()
    {
        return $this->hasMany(ManualAttendance::class);
    }
}