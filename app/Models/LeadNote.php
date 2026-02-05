<?php

namespace App\Models;

use App\Enums\NoteType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * LeadNote Model - Immutable History Log
 * 
 * This model represents immutable notes/history entries for leads.
 * Notes cannot be updated or deleted to maintain audit trail integrity.
 * 
 * @property int $id
 * @property int $lead_id
 * @property int $user_id
 * @property string $note
 * @property NoteType $type
 * @property \Carbon\Carbon $created_at
 */
class LeadNote extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     * We only use created_at, no updated_at
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'lead_id',
        'user_id',
        'note',
        'type',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'type' => NoteType::class,
        'created_at' => 'datetime',
    ];

    /**
     * Boot method - enforce immutability
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-set created_at on creation
        static::creating(function ($note) {
            $note->created_at = now();
        });

        // Prevent updates - notes are immutable
        static::updating(function ($note) {
            throw new \Exception('Lead notes cannot be modified. They are immutable for audit trail purposes.');
        });

        // Prevent direct deletion - notes should never be deleted
        static::deleting(function ($note) {
            // Only allow deletion when parent lead is being deleted (cascade)
            if (!$note->lead || !$note->lead->isForceDeleting()) {
                throw new \Exception('Lead notes cannot be deleted. They are immutable for audit trail purposes.');
            }
        });
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Get the lead this note belongs to
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the user who created this note
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope: Filter by note type
     */
    public function scopeOfType($query, NoteType $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Only call outcome notes
     */
    public function scopeCallOutcomes($query)
    {
        return $query->where('type', NoteType::CallOutcome);
    }

    /**
     * Scope: Only system-generated notes
     */
    public function scopeSystemNotes($query)
    {
        return $query->whereIn('type', [
            NoteType::System,
            NoteType::StatusChange,
            NoteType::Assignment,
        ]);
    }

    /**
     * Scope: Only user-created notes (manual entries)
     */
    public function scopeUserNotes($query)
    {
        return $query->whereNotIn('type', [
            NoteType::System,
            NoteType::StatusChange,
            NoteType::Assignment,
        ]);
    }

    /**
     * Scope: Recent notes (last N days)
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Get formatted timestamp
     */
    public function getFormattedTimeAttribute(): string
    {
        return $this->created_at->format('M d, Y h:i A');
    }

    /**
     * Get relative time (e.g., "2 hours ago")
     */
    public function getRelativeTimeAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Check if note is system-generated
     */
    public function getIsSystemAttribute(): bool
    {
        return $this->type->isSystem();
    }
}
