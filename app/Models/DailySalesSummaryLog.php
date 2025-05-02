<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySalesSummaryLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'daily_sales_summary_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'date',
        'bill_number',
        'user_id',
        'action',
        'field_name',
        'old_value',
        'new_value',
        'action_timestamp'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'action_timestamp' => 'datetime',
    ];

    /**
     * Get the user who made this change.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}