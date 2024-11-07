<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::all();

        return $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
            'title' => $booking->time_slot . ' - ' . $booking->name,
            'start' => $booking->start,
            'end' => $booking->end,
            'time_slot' => $booking->time_slot,
            'name' => $booking->name,
            ];
        });
    }

 

    public function store(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'start' => 'required|date', // Ensure 'start' is required
                'end' => 'nullable|date',
                'time_slot' => 'required|string',
                'name' => 'required|string',
            ]);
    
            // Log the incoming request for debugging
            \Log::info('Booking Data:', $request->all());
    
            // Insert the record into the database
            $start = Carbon::parse($request->start)->format('Y-m-d H:i:s');
            $end = $request->end ? Carbon::parse($request->end)->format('Y-m-d H:i:s') : null;
    
            // Create the booking
            Booking::create([
                'start' => $start,
                'end' => $end,
                'time_slot' => $request->time_slot,
                'name' => $request->name,
            ]);
    
            return response()->json(['message' => 'Booking created successfully!'], 201);
    
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error Creating Booking:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to create booking.'], 500);
        }
    }
    public function update(Request $request, $id)
{
    $request->validate([
        'start' => 'required|date',
            'end' => 'nullable|date',
            'time_slot' => 'required|string',
            'name' => 'required|string',
    ]);

    $booking = Booking::findOrFail($id);

        // Convert ISO datetime to MySQL-compatible format
        $start = Carbon::parse($request->start)->format('Y-m-d H:i:s');
        $end = $request->end ? Carbon::parse($request->end)->format('Y-m-d H:i:s') : null;

        // Update the booking
        $booking->update([
            'start' => $start,
            'end' => $end,
            'time_slot' => $request->time_slot,
            'name' => $request->name,
        ]);


    return response()->json(['message' => 'Booking updated successfully!']);
}
}