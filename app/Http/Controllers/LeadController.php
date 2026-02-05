<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadNote;
use App\Models\User;
use App\Models\Booking;
use App\Models\CustomerFeedback;
use App\Enums\LeadStatus;
use App\Enums\LeadSource;
use App\Enums\LostReason;
use App\Enums\NoteType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display leads dashboard with filtering
     */
    public function index(Request $request)
    {
        $filter = $request->input('filter', 'all');
        $source = $request->input('source');
        $assignee = $request->input('assignee');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $search = $request->input('search');

        $query = Lead::with(['assignee', 'latestNote', 'booking']);

        // Apply status filter
        switch ($filter) {
            case 'need_to_contact':
                $query->where('status', LeadStatus::NeedToContact);
                break;
            case 'not_respond':
                $query->where('status', LeadStatus::NotRespond);
                break;
            case 'called_send_details':
                $query->where('status', LeadStatus::CalledSendDetails);
                break;
            case 'booked':
                $query->won();
                break;
            case 'loss':
                $query->lost();
                break;
            case 'pending':
                $query->pendingForCall();
                break;
            case 'overdue':
                $query->overdue();
                break;
            case 'today':
                $query->today();
                break;
            case 'active':
                $query->active();
                break;
            default:
                // All leads - no filter
                break;
        }

        // Apply source filter
        if ($source) {
            $query->where('source', $source);
        }

        // Apply assignee filter
        if ($assignee) {
            if ($assignee === 'unassigned') {
                $query->unassigned();
            } else {
                $query->assignedTo($assignee);
            }
        }

        // Apply date range filter
        if ($dateFrom) {
            $query->whereDate('inquiry_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('inquiry_date', '<=', $dateTo);
        }

        // Apply search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('requirements', 'like', "%{$search}%");
            });
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get stats for dashboard
        $stats = Lead::getStats();

        // Get users for assignment dropdown
        $users = User::orderBy('name')->get();

        // Get leads grouped by status for accordion view (with notes for display)
        $leadsByStatus = [
            'need_to_contact' => Lead::with(['assignee', 'notes' => fn($q) => $q->latest('created_at')->with('user')])->where('status', LeadStatus::NeedToContact)->orderBy('created_at', 'desc')->get(),
            'not_respond' => Lead::with(['assignee', 'notes' => fn($q) => $q->latest('created_at')->with('user')])->where('status', LeadStatus::NotRespond)->orderBy('created_at', 'desc')->get(),
            'called_send_details' => Lead::with(['assignee', 'notes' => fn($q) => $q->latest('created_at')->with('user')])->where('status', LeadStatus::CalledSendDetails)->orderBy('created_at', 'desc')->get(),
            'booked' => Lead::with(['assignee', 'notes' => fn($q) => $q->latest('created_at')->with('user')])->where('status', LeadStatus::Booked)->orderBy('created_at', 'desc')->get(),
            'loss' => Lead::with(['assignee', 'notes' => fn($q) => $q->latest('created_at')->with('user')])->where('status', LeadStatus::Loss)->orderBy('created_at', 'desc')->get(),
        ];

        // Import completed bookings for feedback and get feedback data
        $this->importCompletedBookingsForFeedback();
        $feedbacksByStatus = [
            'pending' => CustomerFeedback::with(['booking', 'feedbackTakenByUser', 'createdByUser'])
                ->where('status', 'pending')
                ->orderBy('function_date', 'desc')
                ->get(),
            'completed' => CustomerFeedback::with(['booking', 'feedbackTakenByUser', 'createdByUser'])
                ->where('status', 'completed')
                ->orderBy('feedback_taken_at', 'desc')
                ->get(),
        ];

        return view('leads.index', [
            'leads' => $leads,
            'leadsByStatus' => $leadsByStatus,
            'feedbacksByStatus' => $feedbacksByStatus,
            'stats' => $stats,
            'users' => $users,
            'sources' => LeadSource::toArray(),
            'statuses' => LeadStatus::toArray(),
            'lostReasons' => LostReason::toArray(),
            'noteTypes' => NoteType::toArray(),
            'currentFilter' => $filter,
            'currentSource' => $source,
            'currentAssignee' => $assignee,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'search' => $search,
        ]);
    }

    /**
     * Store a new lead
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'phone_number' => 'required|string|max:20',
            'country_code' => 'nullable|string|max:5',
            'source' => 'required|string',
            'check_in' => 'nullable|date',
            'check_out' => 'nullable|date|after_or_equal:check_in',
            'adults' => 'nullable|integer|min:0|max:50',
            'children' => 'nullable|integer|min:0|max:50',
            'requirements' => 'nullable|string|max:2000',
            'interest_level' => 'nullable|integer|min:1|max:5',
            'assigned_to' => 'nullable|exists:users,id',
            'initial_note' => 'nullable|string|max:2000',
        ]);

        DB::beginTransaction();
        try {
            $lead = Lead::create([
                'customer_name' => $validated['customer_name'] ?? null,
                'phone_number' => $validated['phone_number'],
                'country_code' => $validated['country_code'] ?? '+94',
                'inquiry_date' => now(),
                'source' => $validated['source'],
                'check_in' => $validated['check_in'] ?? null,
                'check_out' => $validated['check_out'] ?? null,
                'adults' => $validated['adults'] ?? 0,
                'children' => $validated['children'] ?? 0,
                'requirements' => $validated['requirements'] ?? null,
                'interest_level' => $validated['interest_level'] ?? null,
                'assigned_to' => $validated['assigned_to'] ?? null,
                'status' => LeadStatus::NeedToContact,
            ]);

            // Add initial note if provided
            if (!empty($validated['initial_note'])) {
                $lead->addNote($validated['initial_note'], NoteType::General);
            }

            // Log creation
            $lead->addNote('Lead created from ' . LeadSource::from($validated['source'])->label(), NoteType::System);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lead created successfully',
                    'lead' => $lead->load(['assignee', 'latestNote']),
                ]);
            }

            return redirect()->route('leads.index')
                ->with('success', 'Lead created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating lead: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Error creating lead: ' . $e->getMessage());
        }
    }

    /**
     * Show lead details
     */
    public function show(Lead $lead)
    {
        $lead->load(['assignee', 'notes.user', 'booking']);
        
        return view('leads.show', [
            'lead' => $lead,
            'users' => User::orderBy('name')->get(),
            'statuses' => LeadStatus::toArray(),
            'lostReasons' => LostReason::toArray(),
            'noteTypes' => NoteType::userSelectable(),
        ]);
    }

    /**
     * Update lead
     */
    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'phone_number' => 'required|string|max:20',
            'country_code' => 'nullable|string|max:5',
            'source' => 'required|string',
            'check_in' => 'nullable|date',
            'check_out' => 'nullable|date|after_or_equal:check_in',
            'adults' => 'nullable|integer|min:0|max:50',
            'children' => 'nullable|integer|min:0|max:50',
            'requirements' => 'nullable|string|max:2000',
            'interest_level' => 'nullable|integer|min:1|max:5',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $oldStatus = $lead->status;
        
        $lead->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead updated successfully',
                'lead' => $lead->fresh(['assignee', 'latestNote']),
            ]);
        }

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead updated successfully!');
    }

    /**
     * Update lead status
     */
    public function updateStatus(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'next_follow_up_at' => 'nullable|date',
        ]);

        try {
            // Direct DB update to bypass model events
            $updateData = [
                'status' => $validated['status'],
                'last_communication_at' => now(),
                'updated_at' => now(),
            ];

            if (!empty($validated['next_follow_up_at'])) {
                $updateData['next_follow_up_at'] = Carbon::parse($validated['next_follow_up_at']);
            }

            DB::table('leads')->where('id', $lead->id)->update($updateData);

            return redirect()->route('leads.index')->with('success', 'Status updated successfully!');

        } catch (\Exception $e) {
            return redirect()->route('leads.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Add note to lead
     */
    public function addNote(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'note' => 'required|string|max:2000',
            'type' => 'nullable|string',
        ]);

        $type = !empty($validated['type']) 
            ? NoteType::from($validated['type']) 
            : NoteType::General;

        $note = $lead->addNote($validated['note'], $type);

        // Update last communication timestamp using direct DB to avoid model events
        DB::table('leads')->where('id', $lead->id)->update(['last_communication_at' => now()]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Note added successfully',
                'note' => $note->load('user'),
            ]);
        }

        return redirect()->route('leads.index')->with('success', 'Note added!');
    }

    /**
     * Record call outcome
     */
    public function recordCall(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'outcome' => 'nullable|string|max:2000',
            'status' => 'nullable|string',
            'next_follow_up_at' => 'nullable|date',
        ]);

        try {
            // Direct database update to avoid model events
            $updateData = [
                'last_communication_at' => now(),
            ];

            if (!empty($validated['status'])) {
                $updateData['status'] = $validated['status'];
            }

            if (!empty($validated['next_follow_up_at'])) {
                $updateData['next_follow_up_at'] = Carbon::parse($validated['next_follow_up_at']);
            }

            // Use query builder to bypass model events
            DB::table('leads')->where('id', $lead->id)->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Assign lead to user
     */
    public function assign(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $lead->assignTo($validated['assigned_to']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead assigned successfully',
                'lead' => $lead->fresh(['assignee']),
            ]);
        }

        return back()->with('success', 'Lead assigned successfully!');
    }

    /**
     * Schedule follow-up
     */
    public function scheduleFollowUp(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'next_follow_up_at' => 'required|date|after:now',
            'note' => 'nullable|string|max:2000',
        ]);

        $lead->scheduleFollowUp(
            Carbon::parse($validated['next_follow_up_at']),
            $validated['note'] ?? null
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Follow-up scheduled',
                'lead' => $lead->fresh(['assignee', 'latestNote']),
            ]);
        }

        return back()->with('success', 'Follow-up scheduled!');
    }

    /**
     * Convert lead to booking (mark as won)
     */
    public function convertToBooking(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'booking_id' => 'nullable|exists:bookings,id',
        ]);

        $lead->markAsWon($validated['booking_id'] ?? null);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead converted to booking!',
                'lead' => $lead->fresh(['assignee', 'booking']),
            ]);
        }

        return back()->with('success', 'Lead converted to booking!');
    }

    /**
     * Soft delete lead
     */
    public function destroy(Lead $lead)
    {
        $lead->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead archived successfully',
            ]);
        }

        return redirect()->route('leads.index')
            ->with('success', 'Lead archived successfully!');
    }

    /**
     * Get dashboard stats (AJAX)
     */
    public function getStats()
    {
        return response()->json(Lead::getStats());
    }

    /**
     * Get leads for calendar view
     */
    public function getCalendarData(Request $request)
    {
        $start = $request->input('start', now()->startOfMonth());
        $end = $request->input('end', now()->endOfMonth());

        $leads = Lead::whereBetween('check_in', [$start, $end])
            ->orWhereBetween('next_follow_up_at', [$start, $end])
            ->get();

        $events = [];

        foreach ($leads as $lead) {
            if ($lead->check_in) {
                $events[] = [
                    'id' => 'checkin-' . $lead->id,
                    'title' => ($lead->customer_name ?? 'Unknown') . ' - Check-in',
                    'start' => $lead->check_in->format('Y-m-d'),
                    'end' => $lead->check_out ? $lead->check_out->format('Y-m-d') : null,
                    'color' => '#28a745',
                    'lead_id' => $lead->id,
                ];
            }

            if ($lead->next_follow_up_at && !$lead->status->isFinal()) {
                $events[] = [
                    'id' => 'followup-' . $lead->id,
                    'title' => ($lead->customer_name ?? 'Unknown') . ' - Follow-up',
                    'start' => $lead->next_follow_up_at->format('Y-m-d\TH:i:s'),
                    'color' => $lead->is_overdue ? '#dc3545' : '#ffc107',
                    'lead_id' => $lead->id,
                ];
            }
        }

        return response()->json($events);
    }

    /**
     * Quick add lead (minimal form)
     */
    public function quickAdd(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|max:20',
            'source' => 'required|string',
            'customer_name' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:500',
        ]);

        $lead = Lead::create([
            'phone_number' => $validated['phone_number'],
            'source' => $validated['source'],
            'customer_name' => $validated['customer_name'] ?? null,
            'country_code' => '+94',
            'inquiry_date' => now(),
            'status' => LeadStatus::NeedToContact,
        ]);

        if (!empty($validated['note'])) {
            $lead->addNote($validated['note'], NoteType::General);
        }

        $lead->addNote('Quick lead entry from ' . LeadSource::from($validated['source'])->label(), NoteType::System);

        return response()->json([
            'success' => true,
            'message' => 'Lead added!',
            'lead' => $lead,
        ]);
    }

    /**
     * Import completed bookings as pending feedback entries.
     * Only imports bookings that ended from today going back 5 days.
     */
    private function importCompletedBookingsForFeedback()
    {
        $today = Carbon::today();
        $startDate = $today->copy()->subDays(5);

        $completedBookings = Booking::whereDate('end', '>=', $startDate)
            ->whereDate('end', '<=', $today)
            ->whereNotNull('contact_number')
            ->get();

        foreach ($completedBookings as $booking) {
            // Check including soft-deleted entries so we don't re-import deleted ones
            $exists = CustomerFeedback::withTrashed()->where('booking_id', $booking->id)->exists();

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
}
