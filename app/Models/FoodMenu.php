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
        'welcome_drink',
        'evening_snack',
        'evening_snack_time',
        'dinner',
        'dinner_time',
        'dessert_after_dinner',
        'bed_tea',
        'bed_tea_time',
        'breakfast',
        'breakfast_time',
        'dessert_after_breakfast',
        'morning_snack',
        'morning_snack_time',
        'lunch',
        'lunch_time',
        'dessert_after_lunch',
        'bites',
        'bites_time',
        'created_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'bed_tea_time' => 'datetime',
        'breakfast_time' => 'datetime',
        'morning_snack_time' => 'datetime',
        'lunch_time' => 'datetime',
        'evening_snack_time' => 'datetime',
        'dinner_time' => 'datetime',
        'bites_time' => 'datetime',  // New field
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
     * Always show all meals regardless of booking times
     * This overrides the previous conditional logic
     * 
     * @param string $mealType bed_tea|breakfast|morning_snack|lunch|evening_snack|dinner
     * @return bool
     */
    public function shouldShowMeal($mealType)
    {
        // Always return true to show all meals
        return true;
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
     * Get formatted date
     *
     * @return string
     */
    public function getFormattedDateAttribute()
    {
        return $this->date->format('F j, Y');
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
     * Format time for display
     * 
     * @param string $timeField 
     * @return string|null
     */
    public function getFormattedTime($timeField)
    {
        if (!$this->$timeField) {
            return null;
        }
        
        return Carbon::parse($this->$timeField)->format('g:i A');
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