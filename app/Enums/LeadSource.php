<?php

namespace App\Enums;

/**
 * Lead Source Enum - Laravel 11 Backed Enum
 * 
 * Tracks where the inquiry originated from.
 * Critical for marketing ROI analysis and channel optimization.
 */
enum LeadSource: string
{
    case WhatsApp = 'whatsapp';
    case Facebook = 'facebook';
    case Instagram = 'instagram';
    case WalkIn = 'walk_in';
    case Referral = 'referral';
    case Phone = 'phone';
    case Email = 'email';
    case Website = 'website';
    case Other = 'other';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::WhatsApp => 'WhatsApp',
            self::Facebook => 'Facebook',
            self::Instagram => 'Instagram',
            self::WalkIn => 'Walk-in',
            self::Referral => 'Referral',
            self::Phone => 'Phone Call',
            self::Email => 'Email',
            self::Website => 'Website',
            self::Other => 'Other',
        };
    }

    /**
     * Get icon class for UI display
     */
    public function icon(): string
    {
        return match($this) {
            self::WhatsApp => 'fab fa-whatsapp',
            self::Facebook => 'fab fa-facebook',
            self::Instagram => 'fab fa-instagram',
            self::WalkIn => 'fas fa-walking',
            self::Referral => 'fas fa-user-friends',
            self::Phone => 'fas fa-phone',
            self::Email => 'fas fa-envelope',
            self::Website => 'fas fa-globe',
            self::Other => 'fas fa-question-circle',
        };
    }

    /**
     * Get badge color for UI
     */
    public function color(): string
    {
        return match($this) {
            self::WhatsApp => 'success',
            self::Facebook => 'primary',
            self::Instagram => 'danger',
            self::WalkIn => 'info',
            self::Referral => 'warning',
            self::Phone => 'secondary',
            self::Email => 'dark',
            self::Website => 'info',
            self::Other => 'light',
        };
    }

    /**
     * Check if source is digital/online
     */
    public function isDigital(): bool
    {
        return in_array($this, [
            self::WhatsApp,
            self::Facebook,
            self::Instagram,
            self::Email,
            self::Website,
        ]);
    }

    /**
     * Get all sources as array for dropdowns
     */
    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'icon' => $case->icon(),
            'color' => $case->color(),
        ], self::cases());
    }
}
