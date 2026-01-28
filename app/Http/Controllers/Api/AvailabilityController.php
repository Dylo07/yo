<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    /**
     * Get room availability data for the next 6 months
     * Returns booked rooms and dates for synchronization with public website
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addMonths(6);

        // Get all bookings within the date range
        $bookings = Booking::where(function ($query) use ($startDate, $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                // Bookings that start within the range
                $q->whereBetween('start', [$startDate, $endDate]);
            })->orWhere(function ($q) use ($startDate, $endDate) {
                // Bookings that end within the range
                $q->whereBetween('end', [$startDate, $endDate]);
            })->orWhere(function ($q) use ($startDate, $endDate) {
                // Bookings that span the entire range
                $q->where('start', '<=', $startDate)
                  ->where('end', '>=', $endDate);
            });
        })
        ->orderBy('start', 'asc')
        ->get();

        // Format bookings for API response
        $formattedBookings = $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'start_date' => $booking->start->format('Y-m-d'),
                'end_date' => $booking->end->format('Y-m-d'),
                'rooms' => $this->parseRooms($booking->room_numbers),
                'function_type' => $booking->function_type,
                'guest_count' => $booking->guest_count,
            ];
        });

        // Generate a simple booked dates array for easy consumption
        $bookedDates = $this->generateBookedDatesArray($bookings, $startDate, $endDate);

        // Generate room-specific availability
        $roomAvailability = $this->generateRoomAvailability($bookings, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'generated_at' => Carbon::now()->toIso8601String(),
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total_bookings' => $bookings->count(),
                'total_booked_dates' => count($bookedDates),
            ],
            'bookings' => $formattedBookings,
            'booked_dates' => $bookedDates,
            'room_availability' => $roomAvailability,
        ]);
    }

    /**
     * Parse room numbers from various formats
     *
     * @param mixed $roomNumbers
     * @return array
     */
    private function parseRooms($roomNumbers)
    {
        if (is_array($roomNumbers)) {
            return $roomNumbers;
        }

        if (is_string($roomNumbers)) {
            $decoded = json_decode($roomNumbers, true);
            if (is_array($decoded)) {
                return $decoded;
            }
            // Try comma-separated
            return array_map('trim', explode(',', $roomNumbers));
        }

        return [];
    }

    /**
     * Generate an array of all booked dates
     *
     * @param \Illuminate\Support\Collection $bookings
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function generateBookedDatesArray($bookings, $startDate, $endDate)
    {
        $bookedDates = [];

        foreach ($bookings as $booking) {
            $bookingStart = $booking->start->copy();
            $bookingEnd = $booking->end->copy();

            // Clamp to our date range
            if ($bookingStart->lt($startDate)) {
                $bookingStart = $startDate->copy();
            }
            if ($bookingEnd->gt($endDate)) {
                $bookingEnd = $endDate->copy();
            }

            $current = $bookingStart->copy();
            while ($current->lte($bookingEnd)) {
                $dateStr = $current->format('Y-m-d');
                if (!in_array($dateStr, $bookedDates)) {
                    $bookedDates[] = $dateStr;
                }
                $current->addDay();
            }
        }

        sort($bookedDates);
        return $bookedDates;
    }

    /**
     * Generate room-specific availability data
     *
     * @param \Illuminate\Support\Collection $bookings
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function generateRoomAvailability($bookings, $startDate, $endDate)
    {
        $roomAvailability = [];

        foreach ($bookings as $booking) {
            $rooms = $this->parseRooms($booking->room_numbers);
            $bookingStart = $booking->start->copy();
            $bookingEnd = $booking->end->copy();

            // Clamp to our date range
            if ($bookingStart->lt($startDate)) {
                $bookingStart = $startDate->copy();
            }
            if ($bookingEnd->gt($endDate)) {
                $bookingEnd = $endDate->copy();
            }

            foreach ($rooms as $room) {
                if (!isset($roomAvailability[$room])) {
                    $roomAvailability[$room] = [];
                }

                $current = $bookingStart->copy();
                while ($current->lte($bookingEnd)) {
                    $dateStr = $current->format('Y-m-d');
                    if (!in_array($dateStr, $roomAvailability[$room])) {
                        $roomAvailability[$room][] = $dateStr;
                    }
                    $current->addDay();
                }
            }
        }

        // Sort dates for each room
        foreach ($roomAvailability as $room => $dates) {
            sort($roomAvailability[$room]);
        }

        return $roomAvailability;
    }

    /**
     * Health check endpoint
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function health()
    {
        return response()->json([
            'success' => true,
            'status' => 'healthy',
            'timestamp' => Carbon::now()->toIso8601String(),
            'service' => 'Soba Lanka Ops API',
            'version' => '1.0.0',
        ]);
    }
}
