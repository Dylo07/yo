<?php
// app/Models/GatePass.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class GatePass extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'requested_by',
        'approved_by',
        'gate_pass_number',
        'exit_time',
        'expected_return',
        'actual_return',
        'duration_minutes',
        'purpose',
        'reason',
        'destination',
        'contact_number',
        'status',
        'admin_remarks',
        'approved_at',
        'vehicle_number',
        'emergency_pass',
        'items_carried'
    ];

    protected $casts = [
        'exit_time' => 'datetime',
        'expected_return' => 'datetime',
        'actual_return' => 'datetime',
        'approved_at' => 'datetime',
        'emergency_pass' => 'boolean'
    ];

    // Relationships
    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Accessors
    public function getFormattedPurposeAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->purpose));
    }

    public function getFormattedDurationAttribute()
    {
        if ($this->duration_minutes < 60) {
            return $this->duration_minutes . ' minutes';
        } else {
            $hours = floor($this->duration_minutes / 60);
            $minutes = $this->duration_minutes % 60;
            return $hours . 'h ' . ($minutes > 0 ? $minutes . 'm' : '');
        }
    }

    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'approved':
            case 'active':
                return 'success';
            case 'returned':
                return 'info';
            case 'overdue':
                return 'danger';
            case 'rejected':
                return 'dark';
            default:
                return 'warning';
        }
    }

    public function getIsOverdueAttribute()
    {
        if (in_array($this->status, ['active', 'approved']) && $this->expected_return) {
            return Carbon::now()->isAfter($this->expected_return);
        }
        return false;
    }

    public function getTimeRemainingAttribute()
    {
        if (in_array($this->status, ['active', 'approved']) && $this->expected_return) {
            $remaining = Carbon::now()->diffInMinutes($this->expected_return, false);
            
            if ($remaining < 0) {
                $overdue = abs($remaining);
                if ($overdue < 60) {
                    return $overdue . ' min overdue';
                } else {
                    $hours = floor($overdue / 60);
                    $mins = $overdue % 60;
                    return $hours . 'h ' . ($mins > 0 ? $mins . 'm' : '') . ' overdue';
                }
            } else {
                if ($remaining < 60) {
                    return $remaining . ' min left';
                } else {
                    $hours = floor($remaining / 60);
                    $mins = $remaining % 60;
                    return $hours . 'h ' . ($mins > 0 ? $mins . 'm' : '') . ' left';
                }
            }
        }
        return null;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['approved', 'active']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'active')
                    ->where('expected_return', '<', Carbon::now());
    }

    public function scopeToday($query)
    {
        return $query->whereDate('exit_time', Carbon::today());
    }

    // Methods
    public function generateGatePassNumber()
    {
        $prefix = 'GP';
        $date = Carbon::now()->format('ymd');
        $lastPass = self::whereDate('created_at', Carbon::today())
                       ->orderBy('id', 'desc')
                       ->first();
        
        $sequence = $lastPass ? (int)substr($lastPass->gate_pass_number, -3) + 1 : 1;
        
        return $prefix . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    public function markAsReturned()
    {
        $this->update([
            'actual_return' => Carbon::now(),
            'status' => 'returned'
        ]);
    }

    public function markAsOverdue()
    {
        if ($this->is_overdue && $this->status === 'active') {
            $this->update(['status' => 'overdue']);
        }
    }
}