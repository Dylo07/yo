<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerFeedback extends Model
{
    use HasFactory;

    protected $table = 'customer_feedbacks';

    protected $fillable = [
        'booking_id',
        'customer_name',
        'contact_number',
        'function_type',
        'function_date',
        'status',
        'rating',
        'feedback_notes',
        'feedback_taken_at',
        'feedback_taken_by',
        'created_by',
    ];

    protected $casts = [
        'function_date' => 'date',
        'feedback_taken_at' => 'datetime',
        'rating' => 'integer',
    ];

    // Relationships
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function feedbackTakenByUser()
    {
        return $this->belongsTo(User::class, 'feedback_taken_by');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Accessors
    public function getFormattedPhoneAttribute()
    {
        return '+94 ' . $this->contact_number;
    }

    public function getWhatsappLinkAttribute()
    {
        $phone = preg_replace('/[^0-9]/', '', $this->contact_number);
        if (substr($phone, 0, 1) === '0') {
            $phone = '94' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) !== '94') {
            $phone = '94' . $phone;
        }
        return "https://wa.me/{$phone}";
    }

    // Mark feedback as completed
    public function markAsCompleted($rating, $notes, $userId)
    {
        $this->update([
            'status' => 'completed',
            'rating' => $rating,
            'feedback_notes' => $notes,
            'feedback_taken_at' => now(),
            'feedback_taken_by' => $userId,
        ]);
    }
}
