<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'requested_by',
        'start_date',
        'end_date',
        'start_datetime',
        'end_datetime',
        'hours',
        'is_datetime_based',
        'reason',
        'leave_type',
        'status',
        'admin_remarks',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'approved_at' => 'datetime',
        'is_datetime_based' => 'boolean',
        'hours' => 'decimal:2'
    ];

    /**
     * Get the staff member this leave request is for
     */
    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Get the user who created this request
     */
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the admin who approved/rejected this request
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the number of days for this leave request
     */
    public function getDaysAttribute()
    {
        if ($this->is_datetime_based && $this->hours) {
            // Convert hours to days (assuming 8-hour workday)
            return round($this->hours / 8, 2);
        }
        
        // Fallback to old calculation
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

     public function getFormattedDurationAttribute()
    {
        if ($this->is_datetime_based && $this->hours) {
            if ($this->hours < 8) {
                return $this->hours . ' hours';
            } else {
                $days = floor($this->hours / 8);
                $remainingHours = $this->hours % 8;
                
                if ($remainingHours > 0) {
                    return $days . ' day(s) ' . $remainingHours . ' hours';
                } else {
                    return $days . ' day(s)';
                }
            }
        }
        
        return $this->days . ' day(s)';
    }

    /**
     * Get start time formatted
     */
    public function getFormattedStartTimeAttribute()
    {
        if ($this->is_datetime_based && $this->start_datetime) {
            return $this->start_datetime->format('g:i A');
        }
        return null;
    }

    /**
     * Get end time formatted
     */
    public function getFormattedEndTimeAttribute()
    {
        if ($this->is_datetime_based && $this->end_datetime) {
            return $this->end_datetime->format('g:i A');
        }
        return null;
    }

    /**
     * Get formatted leave type
     */
    public function getFormattedLeaveTypeAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->leave_type));
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'approved':
                return 'success';
            case 'rejected':
                return 'danger';
            default:
                return 'warning';
        }
    }

    /**
     * Check if leave request is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if leave request is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if leave request is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Scope to get pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}