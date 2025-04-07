<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RoomAvailabilityVisualizerController extends Controller
{
    /**
     * Display the room availability view
     */
    public function index()
    {
        try {
            $allRooms = [
                'Ahala', 'Sepalika', 'Sudu Araliya', 'Orchid', 'Olu', 'Nelum', 'Hansa',
                'Mayura', 'Lihini', '121', '122', '123', '124', '106', '107', '108',
                '109', 'CH Room', '130', '131', '132', '133', '134', '101', '102', 
                '103', '104', '105',
            ];
            
            // Group rooms by type
            $roomsByType = [
                'Luxury Rooms' => ['Ahala', 'Sepalika', 'Sudu Araliya', 'Orchid', 'Olu', 'Nelum', 'Hansa', 'Mayura', 'Lihini'],
                'Standard Rooms' => ['121', '122', '123', '124', '106', '107', '108', '109'],
                'Special' => ['CH Room'],
                'Deluxe Rooms' => ['130', '131', '132', '133', '134'],             
                'Economy Rooms' => ['101', '102', '103', '104', '105']
            ];

            // Define time slots
            $timeSlots = [
                'morning' => 'Morning (12:00 AM - 12:00 PM)',
                'afternoon' => 'Afternoon (12:00 PM - 6:00 PM)',
                'evening' => 'Evening (6:00 PM - 12:00 AM)'
            ];

            return view('room-availability', [
                'allRooms' => $allRooms,
                'roomsByType' => $roomsByType,
                'timeSlots' => $timeSlots
            ]);
        } catch (\Exception $e) {
            Log::error('Error in room availability index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('room-availability', [
                'error' => 'Error loading room availability: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get availability data for a date range with time information
     */
    public function getAvailabilityData(Request $request)
    {
        try {
            Log::info('Starting getAvailabilityData method', [
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date')
            ]);
            
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date', $startDate);
            
            if (!$startDate) {
                $startDate = Carbon::today()->format('Y-m-d');
                $endDate = Carbon::today()->addDays(7)->format('Y-m-d');
                
                Log::info('Using default dates', [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]);
            }
            
            // Convert to Carbon instances for easier manipulation
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            
            // Limit to maximum 30 days to prevent performance issues
            if ($end->diffInDays($start) > 30) {
                $end = $start->copy()->addDays(30);
                Log::info('Limited date range to 30 days', [
                    'new_end_date' => $end->format('Y-m-d')
                ]);
            }
            
            $allRooms = [
                'Ahala', 'Sepalika', 'Sudu Araliya', 'Orchid', 'Olu', 'Nelum', 'Hansa',
                'Mayura', 'Lihini', '121', '122', '123', '124', '106', '107', '108',
                '109', 'CH Room', '130', '131', '132', '133', '134', '101', '102', 
                '103', '104', '105',
            ];
            
            Log::info('Fetching bookings for date range', [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d')
            ]);
            
            // Get all bookings that overlap with the date range
            $bookings = Booking::where(function ($query) use ($start, $end) {
                $query->where(function ($q) use ($start, $end) {
                    // Bookings with defined end date that overlap with range
                    $q->where('start', '<=', $end->format('Y-m-d 23:59:59'))
                      ->where('end', '>=', $start->format('Y-m-d 00:00:00'))
                      ->whereNotNull('end');
                      
                    // OR single day bookings within range
                    $q->orWhere(function($sq) use ($start, $end) {
                        $sq->whereDate('start', '>=', $start->format('Y-m-d'))
                           ->whereDate('start', '<=', $end->format('Y-m-d'))
                           ->where(function($ssq) {
                               $ssq->whereNull('end')
                                   ->orWhere('end', 'N/A')
                                   ->orWhere('end', '');
                           });
                    });
                });
            })->get();
            
            Log::info('Found bookings', [
                'count' => $bookings->count()
            ]);
            
            // Initialize the result array - each date will have a list of booked rooms per time slot
            $dateRange = [];
            $current = $start->copy();
            
            while ($current->lte($end)) {
                $dateKey = $current->format('Y-m-d');
                
                $dateRange[$dateKey] = [
                    'date' => $dateKey,
                    'formattedDate' => $current->format('M d, Y'),
                    'dayOfWeek' => $current->format('D'),
                    'timeSlots' => [
                        'morning' => [
                            'label' => 'Morning (12:00 AM - 12:00 PM)',
                            'bookedRooms' => [],
                            'availableRooms' => $allRooms,
                            'bookingGroups' => [] // Add booking groups info
                        ],
                        'afternoon' => [
                            'label' => 'Afternoon (12:00 PM - 6:00 PM)',
                            'bookedRooms' => [],
                            'availableRooms' => $allRooms,
                            'bookingGroups' => [] // Add booking groups info
                        ],
                        'evening' => [
                            'label' => 'Evening (6:00 PM - 12:00 AM)',
                            'bookedRooms' => [],
                            'availableRooms' => $allRooms,
                            'bookingGroups' => [] // Add booking groups info
                        ]
                    ],
                    'bookedRooms' => [],  // For backward compatibility
                    'availableRooms' => $allRooms,  // For backward compatibility
                    'bookingGroups' => []  // Track all booking groups for this day
                ];
                $current->addDay();
            }
            
            Log::info('Date range initialized', [
                'days' => count($dateRange)
            ]);
            
            // Helper function to determine time slot from hour
            $getTimeSlot = function($hour) {
                if ($hour >= 0 && $hour < 12) {
                    return 'morning';
                } elseif ($hour >= 12 && $hour < 18) {
                    return 'afternoon';
                } else {
                    return 'evening';
                }
            };
            
            // Process each booking
            foreach ($bookings as $index => $booking) {
                try {
                    $bookingStart = Carbon::parse($booking->start);
                    $bookingEnd = $booking->end ? Carbon::parse($booking->end) : $bookingStart->copy()->addDay();
                    
                    Log::debug('Processing booking', [
                        'id' => $booking->id,
                        'start' => $bookingStart->format('Y-m-d H:i:s'),
                        'end' => $bookingEnd->format('Y-m-d H:i:s'),
                        'function_type' => $booking->function_type
                    ]);
                    
                    // Get the room numbers
                    $roomArray = $booking->room_numbers;
                    
                    // If it's a string, try to decode it
                    if (is_string($roomArray)) {
                        $roomArray = json_decode($roomArray, true);
                        Log::debug('Decoded room numbers', [
                            'room_array' => $roomArray
                        ]);
                    }
                    
                    // If it's not an array or empty, skip this booking
                    if (!is_array($roomArray) || empty($roomArray)) {
                        Log::warning('Empty room array, skipping booking', [
                            'id' => $booking->id
                        ]);
                        continue;
                    }
                    
                    // Clean up room names
                    $roomArray = array_map(function($room) {
                        return trim($room, '"\'[] ');
                    }, $roomArray);
                    
                    // Create a unique group id for this booking
                    $groupId = $booking->id ?? ('temp_' . $index);
                    
                    // Track affected dates and time slots
                    $currentDate = clone $bookingStart;
                    $currentDate->startOfDay();
                    
                    // Generate a unique color for this booking group
                    // Use a predefined color scheme for different function types
                    $colorMap = [
                        'Wedding' => '#FF5733', // Red-Orange
                        'Night In Group' => '#33FF57', // Bright Green
                        'Day Out' => '#3375FF', // Bright Blue
                        'Couple Package' => '#FF33B8', // Pink
                        'Room Only' => '#33F8FF', // Cyan
                    ];
                    
                    // Use function type color if available, or generate based on booking ID
                    $color = isset($colorMap[$booking->function_type]) 
                        ? $colorMap[$booking->function_type] 
                        : '#' . substr(md5($groupId), 0, 6);
                    
                    // Create booking group info
                    $bookingGroup = [
                        'id' => $groupId,
                        'name' => $booking->name ?? ('Booking #' . $groupId),
                        'function_type' => $booking->function_type ?? 'Unknown',
                        'rooms' => $roomArray,
                        'color' => $color,
                        'start' => $bookingStart->format('Y-m-d H:i:s'),
                        'end' => $bookingEnd->format('Y-m-d H:i:s'),
                        'start_time' => $bookingStart->format('h:i A'),
    'end_time' => $bookingEnd->format('h:i A')
                    ];
                        
                    while ($currentDate->lte($bookingEnd) && $currentDate->lte($end)) {
                        // Skip if date is before our range
                        if ($currentDate->lt($start)) {
                            $currentDate->addDay();
                            continue;
                        }
                        
                        $dateKey = $currentDate->format('Y-m-d');
                        
                        // If this date isn't in our range, move on
                        if (!isset($dateRange[$dateKey])) {
                            $currentDate->addDay();
                            continue;
                        }
                        
                        // Add this booking group to the day's booking groups
                        if (!in_array($bookingGroup, $dateRange[$dateKey]['bookingGroups'])) {
                            $dateRange[$dateKey]['bookingGroups'][] = $bookingGroup;
                        }
                        
                        // Determine which time slots are affected based on booking start/end times
                        $timeSlots = ['morning', 'afternoon', 'evening']; // Default all slots
                        
                        // If this is the booking start date, only include time slots from booking start time onward
                        if ($currentDate->isSameDay($bookingStart)) {
                            $startSlot = $getTimeSlot($bookingStart->hour);
                            $timeSlots = array_filter($timeSlots, function($slot) use ($startSlot, $timeSlots) {
                                // Get index of current slot and start slot
                                $slotIndex = array_search($slot, $timeSlots);
                                $startIndex = array_search($startSlot, $timeSlots);
                                return $slotIndex >= $startIndex;
                            });
                        }
                        
                        // If this is the booking end date, only include time slots up to booking end time
                        if ($currentDate->isSameDay($bookingEnd)) {
                            $endSlot = $getTimeSlot($bookingEnd->hour);
                            $timeSlots = array_filter($timeSlots, function($slot) use ($endSlot, $timeSlots) {
                                // Get index of current slot and end slot
                                $slotIndex = array_search($slot, $timeSlots);
                                $endIndex = array_search($endSlot, $timeSlots);
                                return $slotIndex <= $endIndex;
                            });
                        }
                        
                        // For each affected time slot, mark rooms as booked
                        foreach ($timeSlots as $timeSlot) {
                            // Add to booking groups for this time slot
                            if (!in_array($bookingGroup, $dateRange[$dateKey]['timeSlots'][$timeSlot]['bookingGroups'])) {
                                $dateRange[$dateKey]['timeSlots'][$timeSlot]['bookingGroups'][] = $bookingGroup;
                            }
                            
                            // Add to booked rooms for this time slot
                            foreach ($roomArray as $room) {
                                // Create or update room booking info with booking group reference
                                if (!in_array($room, $dateRange[$dateKey]['timeSlots'][$timeSlot]['bookedRooms'])) {
                                    $dateRange[$dateKey]['timeSlots'][$timeSlot]['bookedRooms'][] = $room;
                                }
                                
                                // Remove from available rooms for this time slot
                                $key = array_search($room, $dateRange[$dateKey]['timeSlots'][$timeSlot]['availableRooms']);
                                if ($key !== false) {
                                    unset($dateRange[$dateKey]['timeSlots'][$timeSlot]['availableRooms'][$key]);
                                    $dateRange[$dateKey]['timeSlots'][$timeSlot]['availableRooms'] = array_values($dateRange[$dateKey]['timeSlots'][$timeSlot]['availableRooms']);
                                }
                            }
                        }
                        
                        // Also update the day level booked/available rooms for backward compatibility
                        // A room is considered booked for the day if it's booked in any time slot
                        $allBookedForDay = array_unique(array_merge(
                            $dateRange[$dateKey]['timeSlots']['morning']['bookedRooms'],
                            $dateRange[$dateKey]['timeSlots']['afternoon']['bookedRooms'],
                            $dateRange[$dateKey]['timeSlots']['evening']['bookedRooms']
                        ));
                        
                        $dateRange[$dateKey]['bookedRooms'] = $allBookedForDay;
                        $dateRange[$dateKey]['availableRooms'] = array_values(array_diff($allRooms, $allBookedForDay));
                        
                        $currentDate->addDay();
                    }
                } catch (\Exception $e) {
                    Log::error('Error processing booking', [
                        'booking_id' => $booking->id ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                    // Continue with the next booking
                    continue;
                }
            }
            
            Log::info('Bookings processed, calculating statistics');
            
            // Calculate availability stats and percentages
            $stats = [
                'totalDays' => count($dateRange),
                'totalRooms' => count($allRooms),
                'daysWithFullAvailability' => 0,
                'daysWithLimitedAvailability' => 0,
                'daysFullyBooked' => 0,
                'averageAvailabilityPercentage' => 0,
                'timeSlotStats' => [
                    'morning' => 0,
                    'afternoon' => 0,
                    'evening' => 0
                ]
            ];
            
            $totalAvailabilityPercentage = 0;
            $totalTimeSlotPercentages = [
                'morning' => 0,
                'afternoon' => 0,
                'evening' => 0
            ];
            
            foreach ($dateRange as $date => $data) {
                // Calculate day-level availability percentage
                $availabilityCount = count($data['availableRooms']);
                $availabilityPercentage = ($availabilityCount / count($allRooms)) * 100;
                $totalAvailabilityPercentage += $availabilityPercentage;
                
                $dateRange[$date]['availabilityPercentage'] = round($availabilityPercentage);
                
                if ($availabilityCount == count($allRooms)) {
                    $stats['daysWithFullAvailability']++;
                } elseif ($availabilityCount == 0) {
                    $stats['daysFullyBooked']++;
                } else {
                    $stats['daysWithLimitedAvailability']++;
                }
                
                // Calculate time slot availability percentages
                foreach (['morning', 'afternoon', 'evening'] as $timeSlot) {
                    $slotAvailableCount = count($data['timeSlots'][$timeSlot]['availableRooms']);
                    $slotPercentage = ($slotAvailableCount / count($allRooms)) * 100;
                    $totalTimeSlotPercentages[$timeSlot] += $slotPercentage;
                    
                    $dateRange[$date]['timeSlots'][$timeSlot]['availabilityPercentage'] = round($slotPercentage);
                }
            }
            
            $stats['averageAvailabilityPercentage'] = round($totalAvailabilityPercentage / $stats['totalDays']);
            
            foreach (['morning', 'afternoon', 'evening'] as $timeSlot) {
                $stats['timeSlotStats'][$timeSlot] = round($totalTimeSlotPercentages[$timeSlot] / $stats['totalDays']);
            }
            
            Log::info('Returning availability data', [
                'date_count' => count($dateRange),
                'stats' => $stats
            ]);
            
            return response()->json([
                'dateRange' => array_values($dateRange),
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating availability data', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to fetch availability data: ' . $e->getMessage()
            ], 500);
        }
    }
}