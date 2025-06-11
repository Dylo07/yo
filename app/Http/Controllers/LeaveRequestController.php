<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of leave requests
     */
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['person', 'requestedBy', 'approvedBy']);

        // Filter by status if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by month if provided
        if ($request->has('month') && $request->month) {
            $month = Carbon::parse($request->month);
            $query->whereYear('start_date', $month->year)
                  ->whereMonth('start_date', $month->month);
        }

        // Filter by staff member if provided
        if ($request->has('person_id') && $request->person_id) {
            $query->where('person_id', $request->person_id);
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get all active staff members for filter dropdown using same approach as attendance
        $staffMembers = Person::whereHas('staffCode', function($query) {
                $query->where('is_active', 1);
            })
            ->where('type', 'individual')
            ->with(['staffCode']) // Eager load staff code
            ->orderBy('name')
            ->get();

        return view('leave-requests.index', compact('leaveRequests', 'staffMembers'));
    }

    /**
     * Show the form for creating a new leave request
     */
    public function create()
    {
        // Get staff members using the same approach as ManualAttendanceController
        $staffMembers = Person::whereHas('staffCode', function($query) {
                $query->where('is_active', 1);
            })
            ->where('type', 'individual')
            ->with(['staffCode', 'staffCategory']) // Eager load relationships
            ->orderBy('name')
            ->get();
            
        return view('leave-requests.create', compact('staffMembers'));
    }

    /**
     * Store a newly created leave request
     */
    public function store(Request $request)
{
    // Base validation rules
    $rules = [
        'person_id' => [
            'required',
            'integer',
            function ($attribute, $value, $fail) {
                $exists = Person::whereHas('staffCode', function($query) {
                        $query->where('is_active', 1);
                    })
                    ->where('type', 'individual')
                    ->where('id', $value)
                    ->exists();
                
                if (!$exists) {
                    $fail('The selected staff member is invalid or inactive.');
                }
            }
        ],
        'reason' => 'required|string|max:1000',
        'leave_type' => 'required|in:sick,annual,emergency,personal,maternity,other',
        'duration_type' => 'required|in:full_day,specific_time'
    ];

    // Conditional validation based on duration type
    if ($request->duration_type === 'full_day') {
        $rules['start_date'] = 'required|date|after_or_equal:today';
        $rules['end_date'] = 'required|date|after_or_equal:start_date';
    } else {
        $rules['start_date_time'] = 'required|date|after_or_equal:today';
        $rules['start_time'] = 'required';
        $rules['end_date_time'] = 'required|date|after_or_equal:start_date_time';
        $rules['end_time'] = 'required';
    }

    $request->validate($rules);

    // Prepare data for storage
    $data = [
        'person_id' => $request->person_id,
        'requested_by' => Auth::id(),
        'reason' => $request->reason,
        'leave_type' => $request->leave_type,
        'status' => 'pending'
    ];

    if ($request->duration_type === 'full_day') {
        $data['start_date'] = $request->start_date;
        $data['end_date'] = $request->end_date;
        $data['is_datetime_based'] = false;
    } else {
        // Create datetime objects
        $startDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->start_date_time . ' ' . $request->start_time);
        $endDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->end_date_time . ' ' . $request->end_time);
        
        // Calculate hours
        $hours = $endDateTime->diffInMinutes($startDateTime) / 60;
        
        $data['start_datetime'] = $startDateTime;
        $data['end_datetime'] = $endDateTime;
        $data['start_date'] = $startDateTime->toDateString();
        $data['end_date'] = $endDateTime->toDateString();
        $data['hours'] = $hours;
        $data['is_datetime_based'] = true;
    }

    // Check for overlapping leave requests
    $this->checkOverlappingLeave($request->person_id, $data);

    LeaveRequest::create($data);

    return redirect()->route('leave-requests.index')
                    ->with('success', 'Leave request submitted successfully!');
}
private function checkOverlappingLeave($personId, $data)
{
    $query = LeaveRequest::where('person_id', $personId)
        ->where('status', 'approved');

    if ($data['is_datetime_based']) {
        $query->where(function ($q) use ($data) {
            $q->where(function ($subQ) use ($data) {
                // Check datetime overlap
                $subQ->where('is_datetime_based', true)
                     ->where(function ($timeQ) use ($data) {
                         $timeQ->whereBetween('start_datetime', [$data['start_datetime'], $data['end_datetime']])
                               ->orWhereBetween('end_datetime', [$data['start_datetime'], $data['end_datetime']])
                               ->orWhere(function ($overlapQ) use ($data) {
                                   $overlapQ->where('start_datetime', '<=', $data['start_datetime'])
                                            ->where('end_datetime', '>=', $data['end_datetime']);
                               });
                     });
            })->orWhere(function ($subQ) use ($data) {
                // Check full day overlap with datetime
                $subQ->where('is_datetime_based', false)
                     ->whereBetween('start_date', [$data['start_date'], $data['end_date']]);
            });
        });
    } else {
        $query->where(function ($q) use ($data) {
            $q->whereBetween('start_date', [$data['start_date'], $data['end_date']])
              ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
              ->orWhere(function ($subQ) use ($data) {
                  $subQ->where('start_date', '<=', $data['start_date'])
                       ->where('end_date', '>=', $data['end_date']);
              });
        });
    }

    if ($query->exists()) {
        throw new \Exception('This staff member already has approved leave during the selected time period.');
    }
}

    /**
     * Display the specified leave request
     */
    public function show(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['person', 'requestedBy', 'approvedBy']);
        return view('leave-requests.show', compact('leaveRequest'));
    }

    /**
     * Show the form for editing the specified leave request
     */
    public function edit(LeaveRequest $leaveRequest)
    {
        // Only allow editing if request is pending
        if ($leaveRequest->status !== 'pending') {
            return redirect()->route('leave-requests.index')
                            ->with('error', 'Cannot edit a leave request that has been processed.');
        }

        // Get staff members using the same approach as ManualAttendanceController
        $staffMembers = Person::whereHas('staffCode', function($query) {
                $query->where('is_active', 1);
            })
            ->where('type', 'individual')
            ->with(['staffCode', 'staffCategory']) // Eager load relationships
            ->orderBy('name')
            ->get();
            
        return view('leave-requests.edit', compact('leaveRequest', 'staffMembers'));
    }

    /**
     * Update the specified leave request
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        // Only allow updating if request is pending
        if ($leaveRequest->status !== 'pending') {
            return redirect()->route('leave-requests.index')
                            ->with('error', 'Cannot update a leave request that has been processed.');
        }

        $request->validate([
            'person_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    // Check if person exists and has active staff code using same logic as attendance
                    $exists = Person::whereHas('staffCode', function($query) {
                            $query->where('is_active', 1);
                        })
                        ->where('type', 'individual')
                        ->where('id', $value)
                        ->exists();
                    
                    if (!$exists) {
                        $fail('The selected staff member is invalid or inactive.');
                    }
                }
            ],
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'leave_type' => 'required|in:sick,annual,emergency,personal,maternity,other'
        ]);

        // Check for overlapping leave requests (excluding current request)
        $overlapping = LeaveRequest::where('person_id', $request->person_id)
            ->where('id', '!=', $leaveRequest->id)
            ->where('status', 'approved')
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                      });
            })
            ->exists();

        if ($overlapping) {
            return back()->withErrors(['error' => 'This staff member already has approved leave during the selected dates.'])
                        ->withInput();
        }

        $leaveRequest->update([
            'person_id' => $request->person_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'leave_type' => $request->leave_type
        ]);

        return redirect()->route('leave-requests.index')
                        ->with('success', 'Leave request updated successfully!');
    }

    /**
     * Approve or reject a leave request (Admin only)
     */
    public function updateStatus(Request $request, LeaveRequest $leaveRequest)
    {
        // Debug logging
        \Log::info('Leave request status update attempt', [
            'user_id' => Auth::id(),
            'is_admin' => Auth::user()->checkAdmin(),
            'leave_request_id' => $leaveRequest->id,
            'requested_status' => $request->input('status'),
            'request_data' => $request->all()
        ]);

        // Check if user is admin
        if (!Auth::user()->checkAdmin()) {
            \Log::warning('Non-admin user tried to update leave request status', [
                'user_id' => Auth::id(),
                'leave_request_id' => $leaveRequest->id
            ]);
            
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_remarks' => 'nullable|string|max:500'
        ]);

        $leaveRequest->update([
            'status' => $request->status,
            'admin_remarks' => $request->admin_remarks,
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        \Log::info('Leave request status updated successfully', [
            'leave_request_id' => $leaveRequest->id,
            'new_status' => $request->status,
            'updated_by' => Auth::id()
        ]);

        $message = $request->status === 'approved' ? 'Leave request approved successfully!' : 'Leave request rejected.';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $request->status,
                'status_badge' => '<span class="badge badge-' . $leaveRequest->status_badge_class . '">' . ucfirst($request->status) . '</span>'
            ]);
        }

        return redirect()->route('leave-requests.index')->with('success', $message);
    }

    /**
     * Remove the specified leave request
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        // Only allow deletion if request is pending or user is admin
        if ($leaveRequest->status !== 'pending' && !Auth::user()->checkAdmin()) {
            return redirect()->route('leave-requests.index')
                            ->with('error', 'Cannot delete a processed leave request.');
        }

        $leaveRequest->delete();

        return redirect()->route('leave-requests.index')
                        ->with('success', 'Leave request deleted successfully!');
    }

    /**
     * Get leave calendar data for AJAX requests
     */
    public function getCalendarData(Request $request)
    {
        try {
            $start = $request->get('start');
            $end = $request->get('end');
            
            \Log::info('Calendar data request', [
                'start' => $start,
                'end' => $end,
                'user_id' => Auth::id()
            ]);

            $leaveRequests = LeaveRequest::with(['person'])
                ->where('status', 'approved')
                ->where(function ($query) use ($start, $end) {
                    $query->whereBetween('start_date', [$start, $end])
                          ->orWhereBetween('end_date', [$start, $end])
                          ->orWhere(function ($q) use ($start, $end) {
                              $q->where('start_date', '<=', $start)
                                ->where('end_date', '>=', $end);
                          });
                })
                ->get();

            \Log::info('Leave requests found for calendar', [
                'count' => $leaveRequests->count(),
                'requests' => $leaveRequests->pluck('id', 'person.name')
            ]);

            $events = [];
            foreach ($leaveRequests as $leave) {
                // Get staff code if available
                $staffName = $leave->person->name;
                if ($leave->person->staffCode) {
                    $staffName = $leave->person->staffCode->staff_code . ' - ' . $leave->person->name;
                }
                
                $events[] = [
                    'id' => $leave->id,
                    'title' => $staffName . ' - ' . $leave->formatted_leave_type,
                    'start' => $leave->start_date->format('Y-m-d'),
                    'end' => $leave->end_date->addDay()->format('Y-m-d'), // FullCalendar end is exclusive
                    'backgroundColor' => $this->getLeaveTypeColor($leave->leave_type),
                    'borderColor' => $this->getLeaveTypeColor($leave->leave_type),
                    'textColor' => '#fff',
                    'extendedProps' => [
                        'leaveType' => $leave->leave_type,
                        'staffName' => $staffName,
                        'reason' => $leave->reason,
                        'days' => $leave->days
                    ]
                ];
            }

            \Log::info('Calendar events prepared', [
                'events_count' => count($events),
                'events' => $events
            ]);

            return response()->json($events);
            
        } catch (\Exception $e) {
            \Log::error('Error getting calendar data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to load calendar data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show calendar view
     */
    public function calendar()
    {
        return view('leave-requests.calendar');
    }

    /**
     * Print leave request details
     */
    public function print(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['person', 'requestedBy', 'approvedBy']);
        return view('leave-requests.print', compact('leaveRequest'));
    }

    /**
     * Get color for leave type
     */
    private function getLeaveTypeColor($leaveType)
    {
        $colors = [
            'sick' => '#dc3545',      // Red
            'annual' => '#28a745',    // Green
            'emergency' => '#fd7e14', // Orange
            'personal' => '#6f42c1',  // Purple
            'maternity' => '#e83e8c', // Pink
            'other' => '#6c757d'      // Gray
        ];

        return $colors[$leaveType] ?? '#6c757d';
    }
}