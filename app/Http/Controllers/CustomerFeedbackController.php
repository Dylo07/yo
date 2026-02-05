<?php

namespace App\Http\Controllers;

use App\Models\CustomerFeedback;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerFeedbackController extends Controller
{
    /**
     * Display the feedback management page.
     * Auto-imports completed bookings from today onwards.
     */
    public function index()
    {
        // Auto-import completed bookings that don't have feedback entries yet
        $this->importCompletedBookings();

        // Get all feedbacks grouped by status
        $feedbacksByStatus = [
            'pending' => CustomerFeedback::with(['booking', 'feedbackTakenByUser', 'createdByUser'])
                ->pending()
                ->orderBy('function_date', 'desc')
                ->get(),
            'completed' => CustomerFeedback::with(['booking', 'feedbackTakenByUser', 'createdByUser'])
                ->completed()
                ->orderBy('feedback_taken_at', 'desc')
                ->get(),
        ];

        return view('feedback.index', compact('feedbacksByStatus'));
    }

    /**
     * Import completed bookings as pending feedback entries.
     * Only imports bookings that ended from today going back 5 days.
     */
    private function importCompletedBookings()
    {
        $today = Carbon::today();
        $startDate = $today->copy()->subDays(5); // Look back 5 days

        // Get completed bookings (end date is in the past or today, starting from 5 days ago)
        $completedBookings = Booking::whereDate('end', '>=', $startDate)
            ->whereDate('end', '<=', $today)
            ->whereNotNull('contact_number')
            ->get();

        foreach ($completedBookings as $booking) {
            // Check if feedback entry already exists for this booking
            $exists = CustomerFeedback::where('booking_id', $booking->id)->exists();

            if (!$exists) {
                CustomerFeedback::create([
                    'booking_id' => $booking->id,
                    'customer_name' => $booking->name ?? 'Unknown',
                    'contact_number' => $booking->contact_number,
                    'function_type' => $booking->function_type,
                    'function_date' => $booking->end ?? $booking->start,
                    'status' => 'pending',
                    'created_by' => auth()->id(),
                ]);
            }
        }
    }

    /**
     * Store a manually added feedback entry.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'function_type' => 'nullable|string|max:255',
            'function_date' => 'required|date',
        ]);

        CustomerFeedback::create([
            'customer_name' => $validated['customer_name'],
            'contact_number' => $validated['contact_number'],
            'function_type' => $validated['function_type'],
            'function_date' => $validated['function_date'],
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('leads.index')->with('success', 'Feedback entry added successfully!');
    }

    /**
     * Mark feedback as completed.
     */
    public function markCompleted(Request $request, CustomerFeedback $feedback)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback_notes' => 'nullable|string|max:2000',
        ]);

        $feedback->markAsCompleted(
            $validated['rating'],
            $validated['feedback_notes'] ?? null,
            auth()->id()
        );

        return redirect()->route('leads.index')->with('success', 'Feedback recorded successfully!');
    }

    /**
     * Delete a feedback entry (admin only).
     */
    public function destroy(CustomerFeedback $feedback)
    {
        $feedback->delete();
        return redirect()->route('leads.index')->with('success', 'Feedback entry deleted!');
    }
}
