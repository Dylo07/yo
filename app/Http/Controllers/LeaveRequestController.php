<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of leave requests with enhanced dashboard
     */
    public function index(Request $request)
    {
        // Build query with relationships
        $query = LeaveRequest::with(['person.staffCode', 'requestedBy', 'approvedBy']);

        // Apply filters
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('leave_type') && $request->leave_type !== '') {
            $query->where('leave_type', $request->leave_type);
        }

        if ($request->has('person_id') && $request->person_id !== '') {
            $query->where('person_id', $request->person_id);
        }

        if ($request->has('month') && $request->month !== '') {
            $month = Carbon::parse($request->month . '-01');
            $query->whereYear('start_date', $month->year)
                  ->whereMonth('start_date', $month->month);
        }

        // Get paginated results
        $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get staff members for filters
        $staffMembers = Person::whereHas('staffCode', function($query) {
                $query->where('is_active', 1);
            })
            ->where('type', 'individual')
            ->with(['staffCode', 'staffCategory'])
            ->orderBy('name')
            ->get();

        // Calculate statistics
        $statistics = $this->calculateStatistics();

        // Calculate leave type distribution
        $leaveTypeStats = $this->calculateLeaveTypeDistribution();

        // Get upcoming leaves
        $upcomingLeaves = $this->getUpcomingLeaves();

        return view('leave-requests.index', compact(
            'leaveRequests', 
            'staffMembers', 
            'statistics', 
            'leaveTypeStats', 
            'upcomingLeaves'
        ));
    }

    /**
     * Calculate dashboard statistics
     */
    private function calculateStatistics()
    {
        $currentMonth = Carbon::now()->format('Y-m');
        
        return [
            'total' => LeaveRequest::count(),
            'pending' => LeaveRequest::where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('status', 'rejected')->count(),
            'this_month' => LeaveRequest::whereYear('start_date', Carbon::now()->year)
                                     ->whereMonth('start_date', Carbon::now()->month)
                                     ->count()
        ];
    }

    /**
     * Calculate leave type distribution for dashboard
     */
    private function calculateLeaveTypeDistribution()
    {
        $totalRequests = LeaveRequest::count();
        
        if ($totalRequests == 0) {
            return [];
        }

        $leaveTypes = LeaveRequest::select('leave_type', DB::raw('count(*) as count'))
            ->groupBy('leave_type')
            ->get();

        $distribution = [];
        foreach ($leaveTypes as $type) {
            $distribution[$type->leave_type] = round(($type->count / $totalRequests) * 100);
        }

        return $distribution;
    }

    /**
     * Get upcoming approved leaves
     */
    private function getUpcomingLeaves($limit = 5)
    {
        return LeaveRequest::with(['person.staffCode'])
            ->where('status', 'approved')
            ->where('start_date', '>=', Carbon::today())
            ->orderBy('start_date', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get leave type badge color class
     */
    public function getLeaveTypeBadgeClass($leaveType)
    {
        $colors = [
            'sick' => 'danger',
            'annual' => 'success', 
            'emergency' => 'warning',
            'personal' => 'info',
            'maternity' => 'primary',
            'other' => 'secondary'
        ];

        return $colors[$leaveType] ?? 'secondary';
    }

    /**
     * Get leave type color for progress bars
     */
    public function getLeaveTypeColor($leaveType)
    {
        $colors = [
            'sick' => 'danger',
            'annual' => 'success',
            'emergency' => 'warning', 
            'personal' => 'info',
            'maternity' => 'primary',
            'other' => 'secondary'
        ];

        return $colors[$leaveType] ?? 'secondary';
    }

    /**
     * Show the form for creating a new leave request
     */
    public function create()
    {
        $staffMembers = Person::whereHas('staffCode', function($query) {
                $query->where('is_active', 1);
            })
            ->where('type', 'individual')
            ->with(['staffCode', 'staffCategory'])
            ->orderBy('name')
            ->get();
            
        return view('leave-requests.create', compact('staffMembers'));
    }

    /**
     * Store a newly created leave request with enhanced validation
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

        // Prepare data for storage - Auto-approve all leave requests
        $data = [
            'person_id' => $request->person_id,
            'requested_by' => Auth::id(),
            'reason' => $request->reason,
            'leave_type' => $request->leave_type,
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ];

        if ($request->duration_type === 'full_day') {
            $data['start_date'] = $request->start_date;
            $data['end_date'] = $request->end_date;
            $data['is_datetime_based'] = false;
        } else {
            // Create datetime objects
            $startDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->start_date_time . ' ' . $request->start_time);
            $endDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->end_date_time . ' ' . $request->end_time);
            
            // Validate time range
            if ($endDateTime <= $startDateTime) {
                return back()->withErrors(['error' => 'End time must be after start time.'])
                            ->withInput();
            }
            
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
        try {
            $this->checkOverlappingLeave($request->person_id, $data);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])
                        ->withInput();
        }

        LeaveRequest::create($data);

        return redirect()->route('leave-requests.index')
                        ->with('success', 'Leave request submitted successfully!');
    }

    /**
     * Enhanced overlap checking
     */
    private function checkOverlappingLeave($personId, $data, $excludeId = null)
    {
        $query = LeaveRequest::where('person_id', $personId)
            ->where('status', 'approved');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

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
                         ->where(function ($dateQ) use ($data) {
                             $dateQ->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                                   ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                                   ->orWhere(function ($overlapQ) use ($data) {
                                       $overlapQ->where('start_date', '<=', $data['start_date'])
                                                ->where('end_date', '>=', $data['end_date']);
                                   });
                         });
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
        $leaveRequest->load(['person.staffCode', 'requestedBy', 'approvedBy']);
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

        $staffMembers = Person::whereHas('staffCode', function($query) {
                $query->where('is_active', 1);
            })
            ->where('type', 'individual')
            ->with(['staffCode', 'staffCategory'])
            ->orderBy('name')
            ->get();
            
        return view('leave-requests.edit', compact('leaveRequest', 'staffMembers'));
    }

    /**
     * Update the specified leave request with enhanced validation
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        // Only allow updating if request is pending
        if ($leaveRequest->status !== 'pending') {
            return redirect()->route('leave-requests.index')
                            ->with('error', 'Cannot update a leave request that has been processed.');
        }

        // Validation rules
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
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'leave_type' => 'required|in:sick,annual,emergency,personal,maternity,other'
        ];

        $request->validate($rules);

        // Prepare update data
        $data = [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_datetime_based' => false
        ];

        // Check for overlapping leave requests (excluding current request)
        try {
            $this->checkOverlappingLeave($request->person_id, $data, $leaveRequest->id);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])
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
     * Enhanced status update with better error handling
     */
    public function updateStatus(Request $request, LeaveRequest $leaveRequest)
    {
        // Enhanced admin check with logging
        if (!Auth::user()->checkAdmin()) {
            \Log::warning('Non-admin user tried to update leave request status', [
                'user_id' => Auth::id(),
                'leave_request_id' => $leaveRequest->id
            ]);
            
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized access'], 403);
            }
            
            return redirect()->route('leave-requests.index')
                            ->with('error', 'Unauthorized access.');
        }

        // Validate request
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_remarks' => 'nullable|string|max:500'
        ]);

        // Check if request is still pending
        if ($leaveRequest->status !== 'pending') {
            $message = 'This leave request has already been processed.';
            
            if ($request->ajax()) {
                return response()->json(['error' => $message], 400);
            }
            
            return redirect()->route('leave-requests.index')
                            ->with('error', $message);
        }

        // Update the leave request
        $leaveRequest->update([
            'status' => $request->status,
            'admin_remarks' => $request->admin_remarks,
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        // Log the action
        \Log::info('Leave request status updated', [
            'leave_request_id' => $leaveRequest->id,
            'new_status' => $request->status,
            'updated_by' => Auth::id(),
            'remarks' => $request->admin_remarks
        ]);

        $message = $request->status === 'approved' 
            ? 'Leave request approved successfully!' 
            : 'Leave request rejected successfully!';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $request->status,
                'status_badge' => '<span class="badge badge-custom bg-' . $leaveRequest->status_badge_class . '">' . ucfirst($request->status) . '</span>'
            ]);
        }

        return redirect()->route('leave-requests.index')
                        ->with('success', $message);
    }

    /**
     * Remove the specified leave request
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        // Enhanced deletion permissions
        $canDelete = $leaveRequest->status === 'pending' || Auth::user()->checkAdmin();
        
        if (!$canDelete) {
            return redirect()->route('leave-requests.index')
                            ->with('error', 'Cannot delete a processed leave request.');
        }

        // Log deletion
        \Log::info('Leave request deleted', [
            'leave_request_id' => $leaveRequest->id,
            'deleted_by' => Auth::id(),
            'original_status' => $leaveRequest->status
        ]);

        $leaveRequest->delete();

        return redirect()->route('leave-requests.index')
                        ->with('success', 'Leave request deleted successfully!');
    }

    /**
     * Get leave calendar data for AJAX requests with better error handling
     */
    public function getCalendarData(Request $request)
    {
        try {
            $start = $request->get('start');
            $end = $request->get('end');
            
            // Validate date parameters
            if (!$start || !$end) {
                return response()->json(['error' => 'Start and end dates are required'], 400);
            }

            $leaveRequests = LeaveRequest::with(['person.staffCode'])
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

            $events = [];
            foreach ($leaveRequests as $leave) {
                // Get staff name with code
                $staffName = $leave->person->name;
                if ($leave->person->staffCode) {
                    $staffName = $leave->person->staffCode->staff_code . ' - ' . $leave->person->name;
                }
                
                $events[] = [
                    'id' => $leave->id,
                    'title' => $staffName . ' - ' . $leave->formatted_leave_type,
                    'start' => $leave->start_date->format('Y-m-d'),
                    'end' => $leave->end_date->addDay()->format('Y-m-d'), // FullCalendar end is exclusive
                    'backgroundColor' => $this->getLeaveTypeCalendarColor($leave->leave_type),
                    'borderColor' => $this->getLeaveTypeCalendarColor($leave->leave_type),
                    'textColor' => '#fff',
                    'extendedProps' => [
                        'leaveType' => $leave->leave_type,
                        'staffName' => $staffName,
                        'reason' => $leave->reason,
                        'duration' => $leave->formatted_duration,
                        'status' => $leave->status
                    ]
                ];
            }

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
        $leaveRequest->load(['person.staffCode', 'requestedBy', 'approvedBy']);
        return view('leave-requests.print', compact('leaveRequest'));
    }

    /**
     * Get calendar colors for leave types
     */
    private function getLeaveTypeCalendarColor($leaveType)
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

    /**
     * Get dashboard analytics data (for future API use)
     */
    public function getDashboardAnalytics(Request $request)
    {
        $period = $request->get('period', 'month'); // week, month, year
        
        try {
            $analytics = [
                'statistics' => $this->calculateStatistics(),
                'leave_type_distribution' => $this->calculateLeaveTypeDistribution(),
                'upcoming_leaves' => $this->getUpcomingLeaves(),
                'monthly_trends' => $this->getMonthlyTrends($period),
                'staff_leave_summary' => $this->getStaffLeaveSummary()
            ];

            return response()->json($analytics);
        } catch (\Exception $e) {
            \Log::error('Error getting dashboard analytics', [
                'error' => $e->getMessage(),
                'period' => $period
            ]);
            
            return response()->json(['error' => 'Failed to load analytics'], 500);
        }
    }

    /**
     * Get monthly trends for analytics
     */
    private function getMonthlyTrends($period = 'month')
    {
        $months = [];
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        
        for ($i = 0; $i < 12; $i++) {
            $month = $startDate->copy()->addMonths($i);
            $count = LeaveRequest::whereYear('start_date', $month->year)
                                ->whereMonth('start_date', $month->month)
                                ->count();
            
            $months[] = [
                'month' => $month->format('M Y'),
                'count' => $count
            ];
        }
        
        return $months;
    }

    /**
     * Get staff leave summary
     */
    private function getStaffLeaveSummary($limit = 10)
    {
        return Person::whereHas('staffCode', function($query) {
                $query->where('is_active', 1);
            })
            ->with(['staffCode'])
            ->withCount(['leaveRequests as total_leaves' => function($query) {
                $query->whereYear('start_date', Carbon::now()->year);
            }])
            ->withCount(['leaveRequests as approved_leaves' => function($query) {
                $query->where('status', 'approved')
                      ->whereYear('start_date', Carbon::now()->year);
            }])
            ->withCount(['leaveRequests as pending_leaves' => function($query) {
                $query->where('status', 'pending');
            }])
            ->orderBy('total_leaves', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Bulk action handler for multiple leave requests
     */
    public function bulkAction(Request $request)
    {
        // Check admin permissions
        if (!Auth::user()->checkAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'action' => 'required|in:approve,reject,delete',
            'leave_request_ids' => 'required|array',
            'leave_request_ids.*' => 'integer|exists:leave_requests,id',
            'admin_remarks' => 'nullable|string|max:500'
        ]);

        $leaveRequestIds = $request->leave_request_ids;
        $action = $request->action;
        $remarks = $request->admin_remarks;
        
        try {
            DB::beginTransaction();
            
            $updated = 0;
            $errors = [];
            
            foreach ($leaveRequestIds as $id) {
                $leaveRequest = LeaveRequest::find($id);
                
                if (!$leaveRequest) {
                    $errors[] = "Leave request #{$id} not found";
                    continue;
                }
                
                if ($action === 'delete') {
                    $leaveRequest->delete();
                    $updated++;
                } elseif (in_array($action, ['approve', 'reject'])) {
                    if ($leaveRequest->status !== 'pending') {
                        $errors[] = "Leave request #{$id} is already processed";
                        continue;
                    }
                    
                    $status = $action === 'approve' ? 'approved' : 'rejected';
                    
                    $leaveRequest->update([
                        'status' => $status,
                        'admin_remarks' => $remarks,
                        'approved_by' => Auth::id(),
                        'approved_at' => now()
                    ]);
                    
                    $updated++;
                }
            }
            
            DB::commit();
            
            $message = "Successfully {$action}d {$updated} leave request(s)";
            if (!empty($errors)) {
                $message .= '. Some requests could not be processed: ' . implode(', ', $errors);
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'updated' => $updated,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            \Log::error('Bulk action failed', [
                'action' => $action,
                'leave_request_ids' => $leaveRequestIds,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Bulk action failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export leave requests to CSV/Excel
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv'); // csv or excel
        
        $query = LeaveRequest::with(['person.staffCode', 'requestedBy', 'approvedBy']);
        
        // Apply same filters as index
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('leave_type') && $request->leave_type !== '') {
            $query->where('leave_type', $request->leave_type);
        }
        
        if ($request->has('person_id') && $request->person_id !== '') {
            $query->where('person_id', $request->person_id);
        }
        
        if ($request->has('month') && $request->month !== '') {
            $month = Carbon::parse($request->month . '-01');
            $query->whereYear('start_date', $month->year)
                  ->whereMonth('start_date', $month->month);
        }
        
        $leaveRequests = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'leave_requests_' . Carbon::now()->format('Y_m_d_H_i_s');
        
        if ($format === 'excel') {
            // If you have Laravel Excel package installed
            // return Excel::download(new LeaveRequestsExport($leaveRequests), $filename . '.xlsx');
        }
        
        // Default CSV export
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];
        
        $callback = function() use ($leaveRequests) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'Staff Name', 'Staff Code', 'Leave Type', 'Start Date', 
                'End Date', 'Duration', 'Status', 'Reason', 'Requested By', 
                'Request Date', 'Approved By', 'Approved Date', 'Admin Remarks'
            ]);
            
            foreach ($leaveRequests as $request) {
                fputcsv($file, [
                    $request->id,
                    $request->person->name,
                    $request->person->staffCode ? $request->person->staffCode->staff_code : '',
                    $request->formatted_leave_type,
                    $request->start_date->format('Y-m-d'),
                    $request->end_date->format('Y-m-d'),
                    $request->formatted_duration,
                    ucfirst($request->status),
                    $request->reason,
                    $request->requestedBy->name,
                    $request->created_at->format('Y-m-d H:i:s'),
                    $request->approvedBy ? $request->approvedBy->name : '',
                    $request->approved_at ? $request->approved_at->format('Y-m-d H:i:s') : '',
                    $request->admin_remarks ?? ''
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get leave request statistics for a specific staff member
     */
    public function getStaffStatistics(Request $request, $personId)
    {
        $person = Person::with(['staffCode'])->findOrFail($personId);
        
        $year = $request->get('year', Carbon::now()->year);
        
        $statistics = [
            'staff_info' => [
                'name' => $person->name,
                'staff_code' => $person->staffCode ? $person->staffCode->staff_code : null,
                'id' => $person->id
            ],
            'year' => $year,
            'total_requests' => LeaveRequest::where('person_id', $personId)
                                          ->whereYear('start_date', $year)
                                          ->count(),
            'approved_requests' => LeaveRequest::where('person_id', $personId)
                                             ->where('status', 'approved')
                                             ->whereYear('start_date', $year)
                                             ->count(),
            'pending_requests' => LeaveRequest::where('person_id', $personId)
                                            ->where('status', 'pending')
                                            ->count(),
            'rejected_requests' => LeaveRequest::where('person_id', $personId)
                                             ->where('status', 'rejected')
                                             ->whereYear('start_date', $year)
                                             ->count(),
            'leave_types' => LeaveRequest::where('person_id', $personId)
                                       ->whereYear('start_date', $year)
                                       ->select('leave_type', DB::raw('count(*) as count'))
                                       ->groupBy('leave_type')
                                       ->get(),
            'monthly_breakdown' => $this->getStaffMonthlyBreakdown($personId, $year)
        ];
        
        return response()->json($statistics);
    }

    /**
     * Get monthly breakdown for specific staff member
     */
    private function getStaffMonthlyBreakdown($personId, $year)
    {
        $months = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $count = LeaveRequest::where('person_id', $personId)
                                ->whereYear('start_date', $year)
                                ->whereMonth('start_date', $month)
                                ->count();
            
            $months[] = [
                'month' => Carbon::create($year, $month, 1)->format('M'),
                'count' => $count
            ];
        }
        
        return $months;
    }

    /**
     * Check leave balance for staff member (if you have leave balance system)
     */
    public function checkLeaveBalance($personId)
    {
        $person = Person::with(['staffCode'])->findOrFail($personId);
        
        // This would depend on your leave balance system
        // Example calculation based on annual leave entitlement
        $currentYear = Carbon::now()->year;
        $annualEntitlement = 21; // Example: 21 days per year
        
        $usedLeave = LeaveRequest::where('person_id', $personId)
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->where('leave_type', 'annual')
            ->sum('days'); // You might need to calculate this differently for datetime-based leaves
        
        $remainingLeave = $annualEntitlement - $usedLeave;
        
        return response()->json([
            'staff_name' => $person->name,
            'staff_code' => $person->staffCode ? $person->staffCode->staff_code : null,
            'year' => $currentYear,
            'annual_entitlement' => $annualEntitlement,
            'used_leave' => $usedLeave,
            'remaining_leave' => $remainingLeave,
            'pending_leave' => LeaveRequest::where('person_id', $personId)
                                         ->where('status', 'pending')
                                         ->where('leave_type', 'annual')
                                         ->sum('days')
        ]);
    }
}