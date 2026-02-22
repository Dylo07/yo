<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GasIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'gas_cylinder_id',
        'quantity',
        'issued_to',
        'issue_date',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'issue_date' => 'date',
    ];

    public function gasCylinder()
    {
        return $this->belongsTo(GasCylinder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
