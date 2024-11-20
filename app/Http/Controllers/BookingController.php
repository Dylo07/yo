<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    /**
     * Fetch all bookings for the calendar.
     */
    public function index()
    {
        $bookings = Booking::all();

        return $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'title' => $booking->time_slot . ' - ' . $booking->name,
                'start' => $booking->start,
                'end' => $booking->end,
                'advance_payment' => $booking->formatted_advance_payment,
                'bill_number' => $booking->bill_number,          // Add this
            'advance_date' => $booking->advance_date ? $booking->advance_date->format('Y-m-d') : null,  // Format the date
            'payment_method' => $booking->payment_method,    // Add this
                
                'name' => $booking->name,
                'function_type' => $booking->function_type,
                'contact_number' => $booking->contact_number,
                'room_numbers' => implode(', ', json_decode($booking->room_numbers) ?? []), // Convert array to string for display
                'guest_count' => $booking->guest_count,
            ];
        });
    }

    /**
     * Store a new booking.
     */
    public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'start' => 'required|date',
            'end' => 'nullable|date',
            'advance_payment' => 'required|numeric|min:0',
            'bill_number' => 'required|string',      // New validation
            'advance_date' => 'required|date',       // New validation
            'payment_method' => 'required|in:online,cash', // New validation
        
            'name' => 'required|string',
            'function_type' => 'required|string',
            'contact_number' => 'required|string|max:15',
            'room_numbers' => 'nullable|array',
            'guest_count' => 'required|string',
        ]);

        $validated['room_numbers'] = json_encode($validated['room_numbers']);
        $validated['user_id'] = auth()->id(); // Add user_id

        \Log::info('Incoming Booking Request:', $validated);

        $start = Carbon::parse($validated['start'])->format('Y-m-d H:i:s');
        $end = isset($validated['end']) ? Carbon::parse($validated['end'])->format('Y-m-d H:i:s') : null;

        Booking::create([
            'start' => $start,
            'end' => $end,
            'advance_payment' => $validated['advance_payment'], // Correctly map advance_payment to time_slot
            'bill_number' => $validated['bill_number'],
            'advance_date' => $validated['advance_date'],
            'payment_method' => $validated['payment_method'],
            
            'name' => $validated['name'],
            'function_type' => $validated['function_type'],
            'contact_number' => $validated['contact_number'],
            'room_numbers' => $validated['room_numbers'],
            'guest_count' => $validated['guest_count'],
            'user_id' => $validated['user_id'], // Add user_id
        ]);

        return response()->json(['message' => 'Booking created successfully!'], 201);
    } catch (\Exception $e) {
        \Log::error('Booking Error:', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Failed to create booking.'], 500);
    }
}


    
    /**
     * Update an existing booking.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'start' => 'required|date',
                'end' => 'nullable|date',
                'advance_payment' => 'required|numeric|min:0',
                'bill_number' => 'required|string',      
            'advance_date' => 'required|date',       
            'payment_method' => 'required|in:online,cash',
                
                'name' => 'required|string',
                'function_type' => 'required|string',
                'contact_number' => 'required|string|max:15',
                'room_numbers' => 'nullable|array',
                'guest_count' => 'required|string',
            ]);

            $validated['room_numbers'] = json_encode($validated['room_numbers']);

            $booking = Booking::findOrFail($id);
            $booking->update($validated);

            return response()->json(['message' => 'Booking updated successfully!']);
        } catch (\Exception $e) {
            Log::error('Booking Update Error:', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);
            return response()->json(['error' => 'Failed to update booking.'], 500);
        }
    }
    public function availableRooms(Request $request)
    {
        $date = $request->query('date');
        if (!$date) {
            return response()->json([], 400); // Bad request if no date is provided
        }
    
        // Define all rooms
        $allRooms = [
            'Ahala', 'Sepalika', 'Sudu Araliya', 'Orchid', 'Olu', 'Nelum', 'Hansa',
            'Mayura', 'Lihini', '121', '122', '123', '124', '106', '107', '108',
            '109', 'CH Room', '130', '131', '132', '133', '134', '101', '102', 
            '103', '104', '105',
        ];
    
        // Get all bookings that overlap with the selected date
        $bookedRooms = Booking::where(function ($query) use ($date) {
            $query->whereDate('start', '<=', $date)
                  ->whereDate('end', '>=', $date)
                  ->orWhere(function ($query) use ($date) {
                      $query->whereDate('start', '=', $date)
                            ->whereNull('end'); // Handle single-day bookings
                  });
        })->pluck('room_numbers');
    
        // Flatten and decode booked rooms into an array
        $bookedRoomsArray = $bookedRooms->flatMap(function ($roomNumbers) {
            return json_decode($roomNumbers, true) ?? [];
        })->unique();
    
        // Calculate available rooms
        $availableRooms = array_diff($allRooms, $bookedRoomsArray->toArray());
    
        return response()->json(array_values($availableRooms)); // Ensure JSON response is clean
    }
    
    public function getLogs()
{
    $logs = Booking::with('user')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($booking) {
            return [
                'function_type' => $booking->function_type,
                'created_at' => $booking->created_at,
                'updated_at' => $booking->updated_at,
                'user_name' => $booking->user ? $booking->user->name : null,
                'advance_payment' => $booking->advance_payment,
                'bill_number' => $booking->bill_number,
                'advance_date' => $booking->advance_date,
                'payment_method' => $booking->payment_method,
                'guest_count' => $booking->guest_count,
                'start' => $booking->start,
                'end' => $booking->end,
            ];
        });

    return response()->json($logs);
}




};
