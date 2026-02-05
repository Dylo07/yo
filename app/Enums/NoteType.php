<?php

namespace App\Enums;

/**
 * Note Type Enum - Laravel 11 Backed Enum
 * 
 * Categorizes lead notes for filtering and reporting.
 * Helps distinguish between manual notes and system-generated entries.
 */
enum NoteType: string
{
    case General = 'general';
    case CallOutcome = 'call_outcome';
    case WhatsAppReply = 'whatsapp_reply';
    case Feedback = 'feedback';
    case System = 'system';
    case StatusChange = 'status_change';
    case Assignment = 'assignment';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::General => 'General Note',
            self::CallOutcome => 'Call Outcome',
            self::WhatsAppReply => 'WhatsApp Reply',
            self::Feedback => 'Guest Feedback',
            self::System => 'System',
            self::StatusChange => 'Status Change',
            self::Assignment => 'Assignment',
        };
    }

    /**
     * Get icon for UI display
     */
    public function icon(): string
    {
        return match($this) {
            self::General => 'fas fa-sticky-note',
            self::CallOutcome => 'fas fa-phone-alt',
            self::WhatsAppReply => 'fab fa-whatsapp',
            self::Feedback => 'fas fa-star',
            self::System => 'fas fa-cog',
            self::StatusChange => 'fas fa-exchange-alt',
            self::Assignment => 'fas fa-user-tag',
        };
    }

    /**
     * Get badge color
     */
    public function color(): string
    {
        return match($this) {
            self::General => 'secondary',
            self::CallOutcome => 'primary',
            self::WhatsAppReply => 'success',
            self::Feedback => 'warning',
            self::System => 'dark',
            self::StatusChange => 'info',
            self::Assignment => 'purple',
        };
    }

    /**
     * Check if note type is system-generated
     */
    public function isSystem(): bool
    {
        return in_array($this, [
            self::System,
            self::StatusChange,
            self::Assignment,
        ]);
    }

    /**
     * Get all types as array for dropdowns
     */
    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'icon' => $case->icon(),
        ], self::cases());
    }

    /**
     * Get only user-selectable types (exclude system types)
     */
    public static function userSelectable(): array
    {
        return array_filter(self::cases(), fn($case) => !$case->isSystem());
    }
}
