<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FoodMenu extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'booking_id',
        'date',
        'breakfast',
        'lunch',
        'evening_snack',
        'dinner',
        'created_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the booking that owns the food menu.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the user that created the food menu.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Determine if a meal should be shown based on booking times
     * 
     * @param string $mealType breakfast|lunch|evening_snack|dinner
     * @return bool
     */
    public function shouldShowMeal($mealType)
    {
        $booking = $this->booking;
        if (!$booking) return false;
        
        $bookingDate = $this->date;
        $startDate = $booking->start->startOfDay();
        $endDate = $booking->end ? $booking->end->startOfDay() : $startDate;
        
        $isCheckInDay = $startDate->isSameDay($bookingDate);
        $isCheckOutDay = $endDate->isSameDay($bookingDate);
        
        $startHour = $isCheckInDay ? $booking->start->hour : 0;
        $endHour = $isCheckOutDay ? ($booking->end ? $booking->end->hour : 23) : 23;
        
        switch ($mealType) {
            case 'breakfast':
                // Show breakfast if check-in is before 10 AM and not check-out day
                // OR if check-out is after 10 AM
                return ($isCheckInDay && $startHour < 10) || 
                       (!$isCheckInDay) || 
                       ($isCheckOutDay && $endHour >= 10);
                
            case 'lunch':
                // Show lunch if check-in is before 3 PM and not check-out day
                // OR if check-out is after 3 PM
                return ($isCheckInDay && $startHour < 15) || 
                       (!$isCheckInDay) || 
                       ($isCheckOutDay && $endHour >= 15);
                
            case 'evening_snack':
                // Show evening snack if not check-out day
                // OR if check-out is after 6 PM
                return (!$isCheckOutDay) || 
                       ($isCheckOutDay && $endHour >= 18);
                
            case 'dinner':
                // Show dinner if not check-out day
                // OR if check-out is after 8 PM
                return (!$isCheckOutDay) || 
                       ($isCheckOutDay && $endHour >= 20);
                
            default:
                return false;
        }
    }
    
    /**
     * Get all food menus for a specific date
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }
    
    /**
     * Get all food menus for active bookings on a specific date
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActiveBookingsForDate($query, $date)
    {
        return $query->whereDate('date', $date)
            ->whereHas('booking', function($q) use ($date) {
                $q->where(function($sq) use ($date) {
                    // Bookings with defined end date that include this day
                    $sq->whereDate('start', '<=', $date)
                      ->whereDate('end', '>=', $date)
                      ->whereNotNull('end');
                    
                    // OR single day bookings on this day
                    $sq->orWhere(function($ssq) use ($date) {
                        $ssq->whereDate('start', $date)
                           ->where(function($sssq) {
                               $sssq->whereNull('end')
                                  ->orWhere('end', 'N/A')
                                  ->orWhere('end', '');
                           });
                    });
                });
            });
    }
    
    /**
     * Get formatted date
     *
     * @return string
     */
    public function getFormattedDateAttribute()
    {
        return $this->date->format('F j, Y');
    }
    
    /**
     * Get a summary of which meals are shown
     *
     * @return array
     */
    public function getMealStatusAttribute()
    {
        return [
            'breakfast' => $this->shouldShowMeal('breakfast'),
            'lunch' => $this->shouldShowMeal('lunch'),
            'evening_snack' => $this->shouldShowMeal('evening_snack'),
            'dinner' => $this->shouldShowMeal('dinner')
        ];
    }
    
    /**
     * Get a list of all assigned rooms for this booking
     *
     * @return array
     */
    public function getAssignedRoomsAttribute()
    {
        if (!$this->booking || !$this->booking->room_numbers) {
            return [];
        }
        
        $rooms = $this->booking->room_numbers;
        
        // If it's a string, try to decode it
        if (is_string($rooms)) {
            $rooms = json_decode($rooms, true);
        }
        
        // If it's not an array or empty, return empty array
        if (!is_array($rooms) || empty($rooms)) {
            return [];
        }
        
        // Clean up room names
        return array_map(function($room) {
            return trim($room, '"\'[] ');
        }, $rooms);
    }
    
    /**
     * Update the last modified time when any meal content is updated
     */
    public function updateLastModified()
    {
        $this->updated_at = Carbon::now();
        $this->save();
    }
}