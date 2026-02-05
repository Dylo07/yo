<?php

namespace App\Models;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\LostReason;
use App\Enums\NoteType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'customer_name',
        'phone_number',
        'country_code',
        'inquiry_date',
        'source',
        'check_in',
        'check_out',
        'adults',
        'children',
        'requirements',
        'status',
        'lost_reason',
        'interest_level',
        'next_follow_up_at',
        'assigned_to',
        'booking_id',
        'feedback_rating',
        'last_communication_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'inquiry_date' => 'datetime',
        'check_in' => 'date',
        'check_out' => 'date',
        'adults' => 'integer',
        'children' => 'integer',
        'interest_level' => 'integer',
        'feedback_rating' => 'integer',
        'next_follow_up_at' => 'datetime',
        'last_communication_at' => 'datetime',
        'status' => LeadStatus::class,
        'source' => LeadSource::class,
        'lost_reason' => LostReason::class,
    ];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [];

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-set inquiry_date if not provided
        static::creating(function ($lead) {
            if (!$lead->inquiry_date) {
                $lead->inquiry_date = now();
            }
        });

        // Log status changes automatically
        static::updating(function ($lead) {
            if ($lead->isDirty('status')) {
                $oldStatus = $lead->getOriginal('status');
                $newStatus = $lead->status;
                
                // Auto-link booking if status changed to 'booked' and booking_id is set
                if ($newStatus === LeadStatus::Booked && $lead->booking_id) {
                    $lead->last_communication_at = now();
                }
            }
        });
    }

    // =========================================================================
    // MUTATORS
    // =========================================================================

    /**
     * Set phone number - strip everything except digits and optional leading +
     */
    protected function phoneNumber(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if (empty($value)) {
                    return null;
                }
                // Keep only digits and leading +
                $cleaned = preg_replace('/[^\d+]/', '', $value);
                // Ensure + is only at the beginning if present
                if (strpos($cleaned, '+') !== false && strpos($cleaned, '+') !== 0) {
                    $cleaned = str_replace('+', '', $cleaned);
                }
                return $cleaned;
            }
        );
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Get formatted phone number with country code
     */
    protected function formattedPhone(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (empty($this->phone_number)) {
                    return null;
                }
                
                $phone = $this->phone_number;
                
                // If country code is stored separately
                if ($this->country_code && !str_starts_with($phone, '+')) {
                    return $this->country_code . ' ' . $phone;
                }
                
                // Format with spaces for readability
                if (strlen($phone) >= 10) {
                    return substr($phone, 0, -7) . ' ' . substr($phone, -7, 3) . ' ' . substr($phone, -4);
                }
                
                return $phone;
            }
        );
    }

    /**
     * Get WhatsApp link for the phone number
     */
    protected function whatsappLink(): Attribute
    {
        return Attribute::make(
            get: function () {
                $phone = preg_replace('/[^\d]/', '', $this->phone_number);
                if ($this->country_code) {
                    $code = preg_replace('/[^\d]/', '', $this->country_code);
                    $phone = $code . $phone;
                }
                return 'https://wa.me/' . $phone;
            }
        );
    }

    /**
     * Get stay duration in nights
     */
    protected function stayDuration(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->check_in && $this->check_out) {
                    return $this->check_in->diffInDays($this->check_out);
                }
                return null;
            }
        );
    }

    /**
     * Get total guests count
     */
    protected function totalGuests(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->adults + $this->children
        );
    }

    /**
     * Check if follow-up is overdue
     */
    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->next_follow_up_at) {
                    return false;
                }
                return $this->next_follow_up_at->isPast() && !$this->status->isFinal();
            }
        );
    }

    /**
     * Get days since last communication
     */
    protected function daysSinceContact(): Attribute
    {
        return Attribute::make(
            get: function () {
                $lastContact = $this->last_communication_at ?? $this->created_at;
                return $lastContact->diffInDays(now());
            }
        );
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope: Leads pending for call (new, call_pending, or overdue follow-up)
     */
    public function scopePendingForCall($query)
    {
        return $query->where(function ($q) {
            $q->whereIn('status', [LeadStatus::NeedToContact, LeadStatus::NotRespond])
              ->orWhere(function ($subQ) {
                  $subQ->whereNotNull('next_follow_up_at')
                       ->where('next_follow_up_at', '<=', now())
                       ->whereNotIn('status', [LeadStatus::Booked, LeadStatus::Loss]);
              });
        });
    }

    /**
     * Scope: Leads needing follow-up (status = follow_up_needed with future date)
     */
    public function scopeNeedsFollowUp($query)
    {
        return $query->where('status', LeadStatus::NotRespond)
                     ->where(function ($q) {
                         $q->whereNull('next_follow_up_at')
                           ->orWhere('next_follow_up_at', '>', now());
                     });
    }

    /**
     * Scope: Leads that can be converted to booking
     */
    public function scopeConvertible($query)
    {
        return $query->whereIn('status', [
            LeadStatus::NotRespond,
            LeadStatus::CalledSendDetails,
        ]);
    }

    /**
     * Scope: Recent leads (last 30 days)
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: Leads created today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope: Active leads (not won or lost)
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [LeadStatus::Booked, LeadStatus::Loss]);
    }

    /**
     * Scope: Won leads
     */
    public function scopeWon($query)
    {
        return $query->where('status', LeadStatus::Booked);
    }

    /**
     * Scope: Lost leads
     */
    public function scopeLost($query)
    {
        return $query->where('status', LeadStatus::Loss);
    }

    /**
     * Scope: Filter by source
     */
    public function scopeFromSource($query, LeadSource $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope: Assigned to specific user
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope: Unassigned leads
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    /**
     * Scope: High interest leads (4-5 rating)
     */
    public function scopeHighInterest($query)
    {
        return $query->where('interest_level', '>=', 4);
    }

    /**
     * Scope: Overdue follow-ups
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('next_follow_up_at')
                     ->where('next_follow_up_at', '<', now())
                     ->whereNotIn('status', [LeadStatus::Booked, LeadStatus::Loss]);
    }

    /**
     * Scope: With check-in date in range
     */
    public function scopeCheckInBetween($query, $start, $end)
    {
        return $query->whereBetween('check_in', [$start, $end]);
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Get all notes for this lead (ordered by newest first)
     */
    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the latest note for this lead
     */
    public function latestNote(): HasOne
    {
        return $this->hasOne(LeadNote::class)->latestOfMany();
    }

    /**
     * Get the assigned user
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the linked booking
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Add a note to this lead
     */
    public function addNote(string $note, NoteType $type = NoteType::General, ?int $userId = null): LeadNote
    {
        return $this->notes()->create([
            'note' => $note,
            'type' => $type,
            'user_id' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Log a status change
     */
    public function logStatusChange(LeadStatus $oldStatus, LeadStatus $newStatus, ?int $userId = null): LeadNote
    {
        $message = "Status changed from '{$oldStatus->label()}' to '{$newStatus->label()}'";
        return $this->addNote($message, NoteType::StatusChange, $userId);
    }

    /**
     * Assign lead to a user
     */
    public function assignTo(?int $userId): bool
    {
        $oldAssignee = $this->assigned_to;
        $this->assigned_to = $userId;
        $saved = $this->save();

        if ($saved && $oldAssignee !== $userId) {
            $user = $userId ? User::find($userId) : null;
            $message = $user 
                ? "Lead assigned to {$user->name}"
                : "Lead unassigned";
            $this->addNote($message, NoteType::Assignment);
        }

        return $saved;
    }

    /**
     * Mark lead as won with optional booking link
     */
    public function markAsWon(?int $bookingId = null): bool
    {
        $oldStatus = $this->status;
        $this->status = LeadStatus::Booked;
        $this->booking_id = $bookingId;
        $this->last_communication_at = now();
        $saved = $this->save();

        if ($saved) {
            $this->logStatusChange($oldStatus, LeadStatus::Booked);
        }

        return $saved;
    }

    /**
     * Mark lead as lost with reason
     */
    public function markAsLost(LostReason $reason, ?string $note = null): bool
    {
        $oldStatus = $this->status;
        $this->status = LeadStatus::Loss;
        $this->lost_reason = $reason;
        $this->last_communication_at = now();
        $saved = $this->save();

        if ($saved) {
            $message = "Lead marked as loss. Reason: {$reason->label()}";
            if ($note) {
                $message .= ". Note: {$note}";
            }
            $this->addNote($message, NoteType::StatusChange);
        }

        return $saved;
    }

    /**
     * Schedule follow-up
     */
    public function scheduleFollowUp(Carbon $dateTime, ?string $note = null): bool
    {
        $this->next_follow_up_at = $dateTime;
        if ($this->status === LeadStatus::NeedToContact) {
            $this->status = LeadStatus::NotRespond;
        }
        $saved = $this->save();

        if ($saved && $note) {
            $this->addNote("Follow-up scheduled for {$dateTime->format('M d, Y h:i A')}. {$note}", NoteType::General);
        }

        return $saved;
    }

    /**
     * Record call outcome
     */
    public function recordCallOutcome(string $outcome, ?LeadStatus $newStatus = null): LeadNote
    {
        $this->last_communication_at = now();
        
        if ($newStatus) {
            $this->status = $newStatus;
        } elseif ($this->status === LeadStatus::NeedToContact) {
            $this->status = LeadStatus::CalledSendDetails;
        }
        
        $this->save();

        return $this->addNote($outcome, NoteType::CallOutcome);
    }

    /**
     * Get statistics for dashboard
     */
    public static function getStats(): array
    {
        return [
            'total_today' => self::today()->count(),
            'pending_calls' => self::pendingForCall()->count(),
            'overdue' => self::overdue()->count(),
            'active' => self::active()->count(),
            'won_this_month' => self::won()->whereMonth('updated_at', now()->month)->count(),
            'lost_this_month' => self::lost()->whereMonth('updated_at', now()->month)->count(),
            'conversion_rate' => self::calculateConversionRate(),
        ];
    }

    /**
     * Calculate conversion rate for the current month
     */
    public static function calculateConversionRate(): float
    {
        $total = self::whereMonth('created_at', now()->month)->count();
        if ($total === 0) {
            return 0;
        }
        $won = self::won()->whereMonth('updated_at', now()->month)->count();
        return round(($won / $total) * 100, 1);
    }
}
