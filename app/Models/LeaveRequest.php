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
        'approved_at' => 'datetime'
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
        return $this->start_date->diffInDays($this->end_date) + 1;
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