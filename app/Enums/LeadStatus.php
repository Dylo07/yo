<?php

namespace App\Enums;

/**
 * Lead Status Enum - Laravel 11 Backed Enum
 * 
 * Simplified workflow:
 * Need To Contact → Not Respond / Called & Send Details → Booked / Loss
 */
enum LeadStatus: string
{
    case NeedToContact = 'need_to_contact';      // New inquiry - needs first contact
    case NotRespond = 'not_respond';              // Called but no answer - need to call again
    case CalledSendDetails = 'called_send_details'; // Called and sent details/quotation
    case Booked = 'booked';                       // Confirmed booking
    case Loss = 'loss';                           // Not interested / Lost

    /**
     * Get human-readable label for the status
     */
    public function label(): string
    {
        return match($this) {
            self::NeedToContact => 'Need To Contact',
            self::NotRespond => 'Not Respond',
            self::CalledSendDetails => 'Called & Send Details',
            self::Booked => 'Booked',
            self::Loss => 'Loss',
        };
    }

    /**
     * Get Sinhala label
     */
    public function sinhalaLabel(): string
    {
        return match($this) {
            self::NeedToContact => 'සම්බන්ධ වීමට',
            self::NotRespond => 'පිළිතුරු නැත',
            self::CalledSendDetails => 'ඇමතූ සහ විස්තර යැව්වා',
            self::Booked => 'වෙන්කළා',
            self::Loss => 'අහිමි',
        };
    }

    /**
     * Get Bootstrap badge color class for UI display
     */
    public function badgeColor(): string
    {
        return match($this) {
            self::NeedToContact => 'warning',
            self::NotRespond => 'secondary',
            self::CalledSendDetails => 'info',
            self::Booked => 'success',
            self::Loss => 'danger',
        };
    }

    /**
     * Get icon class for UI display
     */
    public function icon(): string
    {
        return match($this) {
            self::NeedToContact => 'fas fa-phone-alt',
            self::NotRespond => 'fas fa-phone-slash',
            self::CalledSendDetails => 'fas fa-paper-plane',
            self::Booked => 'fas fa-check-circle',
            self::Loss => 'fas fa-times-circle',
        };
    }

    /**
     * Check if status allows conversion to booking
     */
    public function isConvertible(): bool
    {
        return in_array($this, [
            self::NotRespond,
            self::CalledSendDetails,
        ]);
    }

    /**
     * Check if status requires follow-up action
     */
    public function needsAction(): bool
    {
        return in_array($this, [
            self::NeedToContact,
            self::NotRespond,
        ]);
    }

    /**
     * Check if lead is in a final state (booked or loss)
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::Booked, self::Loss]);
    }

    /**
     * Get all statuses as array for dropdowns
     */
    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'sinhala' => $case->sinhalaLabel(),
            'color' => $case->badgeColor(),
        ], self::cases());
    }

    /**
     * Get statuses that need immediate attention
     */
    public static function actionRequired(): array
    {
        return [self::NeedToContact, self::NotRespond];
    }
}
