<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashierBalanceLog extends Model
{
    protected $fillable = [
        'date',
        'time',
        'balance',
        'note',
        'entered_by',
        'user_id',
    ];
}
