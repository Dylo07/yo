<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\BookingAuditLog;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    /**
     * Fetch all bookings for the calendar.
     */
   public function index()
{
    $bookings = Booking::with(['payments', 'payments.verifier'])->get();
    
    return $bookings->map(function ($booking) {
        $payments = $booking->payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'billNumber' => $payment->bill_number,
                'date' => $payment->payment_date->format('Y-m-d'),
                'method' => $payment->payment_method,
                'isVerified' => $payment->is_verified, 
                'verifiedAt' => $payment->verified_at ? Carbon::parse($payment->verified_at)->format('Y-m-d H:i:s') : null,
                'verifiedBy' => $payment->verifier ? $payment->verifier->name : null
            ];
        });

        // Handle room_numbers as array
        $roomNumbers = is_array($booking->room_numbers) ? 
            implode(', ', $booking->room_numbers) : 
            $booking->room_numbers;

        return [
            'id' => $booking->id,
            'title' => $booking->name,
            'start' => $booking->start,
            'end' => $booking->end,
            'function_type' => $booking->function_type,
            'contact_number' => $booking->contact_number,
            'room_numbers' => json_encode($booking->room_numbers),
            'guest_count' => $booking->guest_count,
            'name' => $booking->name,
            'bites_details' => $booking->bites_details,
            'other_details' => $booking->other_details,
            'advancePayments' => $payments
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
            'name' => 'required|string',
            'function_type' => 'required|string',
            'contact_number' => 'required|string',
            'room_numbers' => 'required|array',
            'guest_count' => 'required|string',
            'advance_payment' => 'required|numeric',
            'bill_number' => 'required|string',
            'advance_date' => 'required|date',
            'payment_method' => 'required|in:online,cash',
            'bites_details' => 'nullable|string',
            'other_details' => 'nullable|string'
        ]);

        \DB::beginTransaction();

        // Check for potential duplicates (same user, same time, same contact within last 5 minutes)
        $recentDuplicate = Booking::where('contact_number', $validated['contact_number'])
            ->where('start', $validated['start'])
            ->where('user_id', auth()->id())
            ->where('created_at', '>', now()->subMinutes(5))
            ->first();

        if ($recentDuplicate) {
            return response()->json([
                'error' => 'A similar booking was just created. Please check your recent bookings.',
                'duplicate_id' => $recentDuplicate->id
            ], 409);
        }

        // Create booking
        $booking = Booking::create([
            'start' => $validated['start'],
            'end' => $validated['end'],
            'name' => $validated['name'],
            'function_type' => $validated['function_type'],
            'contact_number' => $validated['contact_number'],
            'room_numbers' => $validated['room_numbers'],
            'guest_count' => $validated['guest_count'],
            'bites_details' => $validated['bites_details'],
            'other_details' => $validated['other_details'],
            'user_id' => auth()->id()
        ]);

        // Create payment record
        $booking->addPayment([
            'advance_payment' => $validated['advance_payment'],
            'bill_number' => $validated['bill_number'],
            'advance_date' => $validated['advance_date'],
            'payment_method' => $validated['payment_method']
        ]);

        // Log booking creation
        BookingAuditLog::create([
            'booking_id' => $booking->id,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'action' => 'created',
            'field_changed' => null,
            'old_value' => null,
            'new_value' => json_encode($booking->toArray()),
            'ip_address' => $request->ip()
        ]);

        \DB::commit();
        return response()->json(['message' => 'Booking created successfully'], 201);
        
    } catch (\Exception $e) {
        \DB::rollBack();
        Log::error('Booking Creation Error:', ['error' => $e->getMessage()]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
    
    /**
     * Update an existing booking.
     */
   public function update(Request $request, $id)
{
    try {
        $booking = Booking::findOrFail($id);
        $originalBooking = $booking->toArray();
        $user = auth()->user();
        
        // Check if user is trying to change dates/times - only admin can do this
        $isDateTimeChange = $request->has('start') && (
            $request->input('start') !== $booking->start || 
            $request->input('end') !== $booking->end
        );
        
        if ($isDateTimeChange && $user->role !== 'admin') {
            return response()->json([
                'error' => 'Only administrators can change booking dates and times. Please contact an admin.'
            ], 403);
        }
        
        // Base validation rules without dates
        $validationRules = [
            'name' => 'required|string',
            'function_type' => 'required|string',
            'contact_number' => 'required|string',
            'room_numbers' => 'required|array',
            'guest_count' => 'required|string',
            'bites_details' => 'nullable|string',
            'other_details' => 'nullable|string'
        ];

        // Add date validation rules only if they are present in request
        if ($request->has('start')) {
            $validationRules['start'] = 'required|date';
            $validationRules['end'] = 'nullable|date';
        }

        // Add payment validation rules only if payment data is present
        if ($request->has('advance_payment')) {
            $validationRules += [
                'advance_payment' => 'required|numeric',
                'bill_number' => 'required|string',
                'advance_date' => 'required|date',
                'payment_method' => 'required|in:online,cash'
            ];
        }

        $validated = $request->validate($validationRules);

        // Update booking details - only update fields that are present in the request
        $updateData = array_intersect_key($validated, array_flip([
            'name', 'function_type', 'contact_number', 'room_numbers', 'guest_count', 'bites_details', 'other_details'
        ]));

        // Only include dates if they were provided (and user is admin)
        if ($request->has('start') && $user->role === 'admin') {
            $updateData['start'] = $validated['start'];
            $updateData['end'] = $validated['end'];
        }

        // Log each field change
        foreach ($updateData as $field => $newValue) {
            $oldValue = $originalBooking[$field] ?? null;
            if (is_array($newValue)) {
                $newValue = json_encode($newValue);
            }
            if (is_array($oldValue)) {
                $oldValue = json_encode($oldValue);
            }
            if ($oldValue != $newValue) {
                BookingAuditLog::create([
                    'booking_id' => $booking->id,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'action' => 'updated',
                    'field_changed' => $field,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                    'ip_address' => $request->ip()
                ]);
            }
        }

        $booking->update($updateData);

        // Add new payment only if payment data is present
        if ($request->has('advance_payment')) {
            $booking->addPayment([
                'advance_payment' => $validated['advance_payment'],
                'bill_number' => $validated['bill_number'],
                'advance_date' => $validated['advance_date'],
                'payment_method' => $validated['payment_method']
            ]);
            
            // Log payment addition
            BookingAuditLog::create([
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'action' => 'payment_added',
                'field_changed' => 'payment',
                'old_value' => null,
                'new_value' => json_encode([
                    'amount' => $validated['advance_payment'],
                    'bill_number' => $validated['bill_number'],
                    'date' => $validated['advance_date'],
                    'method' => $validated['payment_method']
                ]),
                'ip_address' => $request->ip()
            ]);
        }

        return response()->json(['message' => 'Booking updated successfully']);
    } catch (\Exception $e) {
        Log::error('Booking Update Error:', [
            'error' => $e->getMessage(),
            'request' => $request->all()
        ]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    public function availableRooms(Request $request)
{
    $startDate = $request->query('date');
    $endDate = $request->query('endDate', $startDate);
    $excludeBookingId = $request->query('excludeBooking');

    if (!$startDate) {
        return response()->json([], 400);
    }

    $allRooms = [
        'Ahala', 'Sepalika', 'Sudu Araliya', 'Orchid', 'Olu', 'Nelum', 'Hansa',
        'Mayura', 'Lihini', '121', '122', '123', '124', '106', '107', '108',
        '109', 'CH Room', '130', '131', '132', '133', '134', '101', '102', 
        '103', '104', '105',
    ];

    // Parse dates properly for ISO datetime strings
    $startDateTime = Carbon::parse($startDate);
    $endDateTime = Carbon::parse($endDate);

    // Get bookings that overlap with the requested time period
    $bookings = Booking::where(function ($query) use ($startDateTime, $endDateTime, $excludeBookingId) {
        $query->where(function ($q) use ($startDateTime, $endDateTime) {
            // Check for bookings with end date that overlap
            $q->where(function($overlaps) use ($startDateTime, $endDateTime) {
                $overlaps->where('start', '<=', $endDateTime)
                        ->where('end', '>=', $startDateTime)
                        ->whereNotNull('end')
                        ->where('end', '!=', 'null')
                        ->where('end', '!=', '')
                        ->where('end', '!=', 'N/A');
            });
            
            // OR single day bookings that occur within our time range
            $q->orWhere(function($single) use ($startDateTime, $endDateTime) {
                $single->whereBetween('start', [$startDateTime, $endDateTime])
                       ->where(function($nullEnd) {
                           $nullEnd->whereNull('end')
                                  ->orWhere('end', 'N/A')
                                  ->orWhere('end', '')
                                  ->orWhere('end', 'null');
                       });
            });
        });
        
        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }
    })->get();

    // Get all booked rooms from the bookings
    $bookedRooms = [];
    foreach ($bookings as $booking) {
        $roomArray = $booking->room_numbers;
        
        // Handle different room_numbers formats
        if (is_string($roomArray)) {
            $decoded = json_decode($roomArray, true);
            $roomArray = $decoded ?: [$roomArray];
        }
        
        if (is_array($roomArray)) {
            foreach ($roomArray as $room) {
                $room = trim($room, '"\'[] ');
                if (!empty($room)) {
                    $bookedRooms[] = $room;
                }
            }
        }
    }

    // Remove duplicates and filter out empty values
    $bookedRooms = array_unique(array_filter($bookedRooms));

    // Debug logging for time slots
    \Log::info('Time Slot Availability Check:', [
        'start' => $startDateTime->toISOString(),
        'end' => $endDateTime->toISOString(),
        'bookings_found' => $bookings->count(),
        'booked_rooms' => $bookedRooms,
        'exclude_booking' => $excludeBookingId
    ]);

    $availableRooms = array_values(array_diff($allRooms, $bookedRooms));

    return response()->json($availableRooms);
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
                'bites_details' => $booking->bites_details,
                'other_details' => $booking->other_details,
                'start' => $booking->start,
                'end' => $booking->end,
            ];
        });

    return response()->json($logs);
}


public function printConfirmation($id)
{
    $booking = Booking::with(['payments' => function($query) {
        $query->orderBy('payment_date', 'asc')
              ->with('verifier'); // Load the verifier relationship
    }])->findOrFail($id);
    
    // Get the latest payment
    $latestPayment = $booking->payments->last();
    
    $data = [
        'booking' => $booking,
        'payment' => $latestPayment,
        'isWedding' => $booking->function_type === 'Wedding'
    ];
    
    return view('bookings.print-confirmation', $data);
}
// app/Http/Controllers/BookingController.php
public function toggleVerification(Request $request, $paymentId)
{
    $payment = BookingPayment::findOrFail($paymentId);
    
    // Only admins can verify payments
    if (auth()->user()->role !== 'admin') {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    // Toggle verification status
    if ($payment->is_verified) {
        $payment->update([
            'is_verified' => false,
            'verified_at' => null,
            'verified_by' => null
        ]);
        $message = 'Payment verification removed';
    } else {
        $payment->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => auth()->id()
        ]);
        $message = 'Payment verified successfully';
    }
    
    return response()->json([
        'success' => true,
        'message' => $message,
        'payment' => [
            'id' => $payment->id,
            'is_verified' => $payment->is_verified,
            'verified_at' => $payment->verified_at,
            'verified_by' => $payment->verified_by ? auth()->user()->name : null
        ]
    ]);
}
public function getRecentBookings(Request $request)
{
    $limit = $request->get('limit', 5);
    $days = $request->get('days', 30); // Changed from hours to days, default 30 days
    
    $cutoffTime = Carbon::now()->subDays($days); // Changed from subHours to subDays
    
    $recentBookings = Booking::with(['user', 'payments' => function($query) {
        $query->latest()->take(1); // Get latest payment only
    }])
    ->where(function($query) use ($cutoffTime) {
        $query->where('created_at', '>=', $cutoffTime)
              ->orWhere('updated_at', '>=', $cutoffTime);
    })
    ->orderByRaw('GREATEST(created_at, updated_at) DESC')
    ->limit($limit)
    ->get();

    return $recentBookings->map(function($booking) use ($cutoffTime) {
        $latestPayment = $booking->payments->first();
        
        // Determine if booking is new or updated
        $isNew = $booking->created_at >= $cutoffTime && 
                 $booking->created_at->eq($booking->updated_at);
        $isUpdated = $booking->updated_at >= $cutoffTime && 
                    !$booking->created_at->eq($booking->updated_at);

        return [
            'id' => $booking->id,
            'function_type' => $booking->function_type,
            'name' => $booking->name,
            'contact_number' => $booking->contact_number,
            'guest_count' => $booking->guest_count,
            'room_numbers' => $booking->room_numbers,
            'start' => $booking->start->toISOString(),
            'end' => $booking->end ? $booking->end->toISOString() : null,
            'created_at' => $booking->created_at->toISOString(),
            'updated_at' => $booking->updated_at->toISOString(),
            'user_name' => $booking->user ? $booking->user->name : 'Unknown',
            'advance_payment' => $latestPayment ? $latestPayment->amount : 0,
            'isNew' => $isNew,
            'isUpdated' => $isUpdated,
            'formatted_start' => $booking->start->format('M j, Y g:i A'),
            'formatted_end' => $booking->end ? $booking->end->format('M j, Y g:i A') : null,
            'time_ago' => $this->getTimeAgo($isUpdated ? $booking->updated_at : $booking->created_at)
        ];
    });
}

/**
 * Helper method to get human-readable time difference
 */
private function getTimeAgo($date)
{
    $now = Carbon::now();
    $diffInMinutes = $date->diffInMinutes($now);
    
    if ($diffInMinutes < 1) {
        return 'Just now';
    } elseif ($diffInMinutes < 60) {
        return $diffInMinutes . ' min' . ($diffInMinutes > 1 ? 's' : '') . ' ago';
    } elseif ($diffInMinutes < 1440) { // 24 hours
        $hours = floor($diffInMinutes / 60);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } else {
        return $date->format('M j, g:i A');
    }
}

/**
 * Get booking statistics for dashboard
 */
public function getBookingStats()
{
    $today = Carbon::today();
    $thisWeek = Carbon::now()->startOfWeek();
    $thisMonth = Carbon::now()->startOfMonth();
    
    return [
        'today' => [
            'total' => Booking::whereDate('created_at', $today)->count(),
            'new' => Booking::whereDate('created_at', $today)
                           ->where('created_at', '=', DB::raw('updated_at'))
                           ->count(),
            'updated' => Booking::whereDate('updated_at', $today)
                              ->where('created_at', '!=', DB::raw('updated_at'))
                              ->count()
        ],
        'week' => [
            'total' => Booking::where('created_at', '>=', $thisWeek)->count(),
            'revenue' => Booking::with('payments')
                              ->where('created_at', '>=', $thisWeek)
                              ->get()
                              ->sum(function($booking) {
                                  return $booking->payments->sum('amount');
                              })
        ],
        'month' => [
            'total' => Booking::where('created_at', '>=', $thisMonth)->count(),
            'revenue' => Booking::with('payments')
                              ->where('created_at', '>=', $thisMonth)
                              ->get()
                              ->sum(function($booking) {
                                  return $booking->payments->sum('amount');
                              })
        ]
    ];
}

/**
 * Get audit logs for a specific booking
 */
public function getAuditLogs($id)
{
    $logs = BookingAuditLog::where('booking_id', $id)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($log) {
            return [
                'id' => $log->id,
                'user_name' => $log->user_name,
                'action' => $log->action,
                'field_changed' => $log->field_changed,
                'old_value' => $log->old_value,
                'new_value' => $log->new_value,
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at->format('M j, Y g:i A'),
                'time_ago' => $this->getTimeAgo($log->created_at)
            ];
        });
    
    return response()->json(['logs' => $logs]);
}

/**
 * Check if current user can edit dates
 */
public function canEditDates()
{
    $user = auth()->user();
    return response()->json([
        'can_edit_dates' => $user && $user->role === 'admin'
    ]);
}

/**
 * Search bookings for the "Add to Existing Booking" feature on advance payment receipt.
 */
public function searchBookings(Request $request)
{
    $query = $request->get('q', '');
    
    $bookings = Booking::with(['payments'])
        ->where(function($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('function_type', 'LIKE', "%{$query}%")
              ->orWhere('contact_number', 'LIKE', "%{$query}%")
              ->orWhere('id', 'LIKE', "%{$query}%");
        })
        ->orderBy('start', 'desc')
        ->limit(20)
        ->get();

    return $bookings->map(function($booking) {
        $totalPaid = $booking->payments->sum('amount');
        return [
            'id' => $booking->id,
            'name' => $booking->name,
            'function_type' => $booking->function_type,
            'contact_number' => $booking->contact_number,
            'guest_count' => $booking->guest_count,
            'start' => $booking->start ? $booking->start->format('M j, Y g:i A') : 'N/A',
            'end' => $booking->end ? $booking->end->format('M j, Y g:i A') : 'N/A',
            'total_paid' => $totalPaid,
            'payment_count' => $booking->payments->count(),
        ];
    });
}

/**
 * Add a payment to an existing booking (called from advance payment receipt page).
 */
public function addPaymentFromReceipt(Request $request)
{
    $request->validate([
        'booking_id' => 'required|exists:bookings,id',
        'amount' => 'required|numeric|min:0',
        'bill_number' => 'required|string',
        'payment_date' => 'required|date',
        'payment_method' => 'required|in:online,cash',
    ]);

    try {
        \DB::beginTransaction();

        $booking = Booking::findOrFail($request->booking_id);

        $payment = $booking->addPayment([
            'advance_payment' => $request->amount,
            'bill_number' => $request->bill_number,
            'advance_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
        ]);

        // Log the action
        BookingAuditLog::create([
            'booking_id' => $booking->id,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'action' => 'payment_added',
            'field_changed' => 'payment',
            'old_value' => null,
            'new_value' => json_encode([
                'amount' => $request->amount,
                'bill_number' => $request->bill_number,
                'date' => $request->payment_date,
                'method' => $request->payment_method,
                'source' => 'advance_payment_receipt'
            ]),
            'ip_address' => $request->ip()
        ]);

        \DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Payment of Rs " . number_format($request->amount, 2) . " added to booking #{$booking->id} ({$booking->function_type} - {$booking->name})",
            'booking' => [
                'id' => $booking->id,
                'name' => $booking->name,
                'function_type' => $booking->function_type,
            ]
        ]);
    } catch (\Exception $e) {
        \DB::rollBack();
        Log::error('Add Payment From Receipt Error:', ['error' => $e->getMessage()]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
};
