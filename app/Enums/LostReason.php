<?php

namespace App\Enums;

/**
 * Lost Reason Enum - Laravel 11 Backed Enum
 * 
 * Tracks why a lead was lost. Essential for identifying patterns
 * and improving conversion rates.
 */
enum LostReason: string
{
    case Price = 'price';
    case Competitor = 'competitor';
    case DatesUnavailable = 'dates_unavailable';
    case NoAnswer = 'no_answer';
    case NotInterested = 'not_interested';
    case WrongNumber = 'wrong_number';
    case Duplicate = 'duplicate';
    case ChangedPlans = 'changed_plans';
    case Other = 'other';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::Price => 'Price too high',
            self::Competitor => 'Chose competitor',
            self::DatesUnavailable => 'Dates unavailable',
            self::NoAnswer => 'No answer / Unreachable',
            self::NotInterested => 'Not interested',
            self::WrongNumber => 'Wrong number',
            self::Duplicate => 'Duplicate lead',
            self::ChangedPlans => 'Changed travel plans',
            self::Other => 'Other reason',
        };
    }

    /**
     * Check if reason is actionable (could be recovered)
     */
    public function isRecoverable(): bool
    {
        return in_array($this, [
            self::Price,
            self::DatesUnavailable,
            self::NoAnswer,
            self::ChangedPlans,
        ]);
    }

    /**
     * Get all reasons as array for dropdowns
     */
    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
