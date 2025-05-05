<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManualAttendance;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ManualAttendanceController extends Controller
{
    public function index()
    {
        // Get staff members who have staff codes using relationship
        $staff = Person::whereHas('staffCode', function($query) {
                $query->where('is_active', 1);
            })
            ->where('type', 'individual')
            ->orderBy('name')
            ->get();
    
        $today = Carbon::now()->format('Y-m-d');
        
        $attendances = ManualAttendance::whereDate('created_at', $today)
                ->with('person')
                ->get()
                ->keyBy('person_id');
    
        // Add debug logging
        \Log::info('Staff count: ' . $staff->count());
        \Log::info('Staff members:', $staff->pluck('name')->toArray());
    
        return view('attendance.manual.index', compact('staff', 'attendances'));
    }

    public function markAttendance(Request $request)
    {
        $request->validate([
            'person_id' => 'required|exists:persons,id',
            'status' => 'required|in:present,half,absent',
            'remarks' => 'nullable|string|max:255',
            'attendance_date' => 'required|date'
        ]);

        $markDate = Carbon::parse($request->attendance_date)->startOfDay();
        
        // Only admin can mark attendance for previous dates
        if (!Auth::user()->checkAdmin() && $markDate->format('Y-m-d') !== Carbon::now()->format('Y-m-d')) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can mark attendance for previous dates'
            ], 403);
        }

        // Prevent marking attendance for future dates
        if ($markDate->isAfter(Carbon::now())) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot mark attendance for future dates'
            ], 403);
        }

        $attendance = ManualAttendance::updateOrCreate(
            [
                'person_id' => $request->person_id,
                'attendance_date' => $markDate->format('Y-m-d')
            ],
            [
                'status' => $request->status,
                'remarks' => $request->remarks,
                'marked_by' => Auth::id()
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Attendance marked successfully',
            'attendance' => $attendance,
            'status_badge' => view('attendance.manual.partials.status-badge', [
                'status' => $attendance->status
            ])->render()
        ]);
    }

    /**
     * Toggle attendance between present/absent for a staff member on a selected date
     */
    public function toggleAttendance(Request $request)
{
    $request->validate([
        'person_id' => 'required|exists:persons,id',
        'attendance_date' => 'required|date'
    ]);

    $markDate = Carbon::parse($request->attendance_date)->startOfDay();
    
    // Only admin can mark attendance for previous dates
    if (!Auth::user()->checkAdmin()) {
        return response()->json([
            'success' => false,
            'message' => 'Only administrators can toggle attendance for previous dates'
        ], 403);
    }

    // Prevent marking attendance for future dates
    if ($markDate->isAfter(Carbon::now())) {
        return response()->json([
            'success' => false,
            'message' => 'Cannot mark attendance for future dates'
        ], 403);
    }

    // Find existing attendance record for this date and person
    $existingAttendance = ManualAttendance::where([
        'person_id' => $request->person_id,
        'attendance_date' => $markDate->format('Y-m-d')
    ])->first();

    // Determine the new status based on current status
    $newStatus = 'present'; // Default status for first click

    if ($existingAttendance) {
        // Toggle between present → half → absent → not marked (delete)
        if ($existingAttendance->status === 'present') {
            $newStatus = 'half';
        } elseif ($existingAttendance->status === 'half') {
            $newStatus = 'absent';
        } elseif ($existingAttendance->status === 'absent') {
            // Delete record (not marked)
            $existingAttendance->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Attendance record removed',
                'status' => 'not_marked'
            ]);
        }
    }

    // Update or create attendance record
    $attendance = ManualAttendance::updateOrCreate(
        [
            'person_id' => $request->person_id,
            'attendance_date' => $markDate->format('Y-m-d')
        ],
        [
            'status' => $newStatus,
            'remarks' => $existingAttendance->remarks ?? '',
            'marked_by' => Auth::id()
        ]
    );

    return response()->json([
        'success' => true,
        'message' => 'Attendance toggled successfully',
        'attendance' => $attendance,
        'status' => $newStatus,
        'status_badge' => view('attendance.manual.partials.status-badge', [
            'status' => $attendance->status
        ])->render()
    ]);
}
    
    /**
     * Get attendance history for a specific staff member
     */
    public function getStaffAttendanceHistory(Request $request, $personId)
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);
        
        // Only admin can view attendance history
        if (!Auth::user()->checkAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can view attendance history'
            ], 403);
        }
        
        $person = Person::findOrFail($personId);
        
        // Set the month or default to current month
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
        
        // Get attendance for the month
        $attendances = ManualAttendance::where('person_id', $personId)
            ->whereBetween('attendance_date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ])
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->attendance_date)->format('Y-m-d');
            });
        
        // Generate all dates in the month
        $dates = [];
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateKey = $currentDate->format('Y-m-d');
            $dates[$dateKey] = [
                'date' => $dateKey,
                'formatted_date' => $currentDate->format('j M Y'),
                'day_name' => $currentDate->format('l'),
                'status' => isset($attendances[$dateKey]) ? $attendances[$dateKey]->status : 'not_marked',
                'remarks' => isset($attendances[$dateKey]) ? $attendances[$dateKey]->remarks : '',
            ];
            $currentDate->addDay();
        }
        
        return response()->json([
            'success' => true,
            'person' => $person,
            'dates' => $dates,
            'attendances' => $attendances
        ]);
    }


    /**
 * Search for persons in the database
 */
/**
 * Search for persons in the database
 */
public function searchPersons(Request $request)
{
    $request->validate([
        'query' => 'required|string|min:1|max:100',
    ]);

    // Only admin can search persons
    if (!Auth::user()->checkAdmin()) {
        return response()->json([
            'success' => false,
            'message' => 'Only administrators can search persons'
        ], 403);
    }

    $query = $request->input('query');
    
    // Search by ID or name
    $persons = Person::where('id', 'LIKE', $query)
        ->orWhere('name', 'LIKE', "%{$query}%")
        ->orderBy('name')
        ->limit(10)
        ->get(['id', 'name', 'type', 'phone']);
    
    return response()->json([
        'success' => true,
        'persons' => $persons
    ]);
}



public function showAddStaffForm()
{
    $availablePersons = Person::whereDoesntHave('staffCode')
        ->orWhereHas('staffCode', function($query) {
            $query->where('is_active', 0);
        })
        ->where('type', 'individual')
        ->orderBy('name')
        ->get();
        
    return view('attendance.manual.add-staff', compact('availablePersons'));
}



/**
 * Add a new staff member
 */
public function addStaffMember(Request $request)
{
    $request->validate([
        'person_id' => 'required|exists:persons,id',
        'staff_code' => 'required|string|max:20',
    ]);

    // Only admin can add staff members
    if (!Auth::user()->checkAdmin()) {
        return redirect()->back()->with('error', 'Only administrators can add staff members');
    }

    // Check if person is already a staff member
    $existingStaff = \App\Models\StaffCode::where('person_id', $request->person_id)->first();
    
    if ($existingStaff) {
        // Update existing staff code
        $existingStaff->staff_code = $request->staff_code;
        $existingStaff->is_active = $request->has('is_active') ? 1 : 0;
        $existingStaff->save();
        
        return redirect()->route('attendance.manual.index')->with('success', 'Staff member updated successfully');
    }
    
    // Create new staff code
    $staffCode = new \App\Models\StaffCode();
    $staffCode->person_id = $request->person_id;
    $staffCode->staff_code = $request->staff_code;
    $staffCode->is_active = $request->has('is_active') ? 1 : 0;
    $staffCode->save();
    
    return redirect()->route('attendance.manual.index')->with('success', 'Staff member added successfully');
}
    public function report(Request $request)
    {
        // Get staff members who have staff codes
        $staff = Person::whereHas('staffCode', function($query) {
            $query->where('is_active', 1);
        })
        ->where('type', 'individual')
        ->orderBy('name')
        ->get();

        // Default to current month if no dates selected
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();
        
        // Generate all dates in the range
        $dates = [];
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dates[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        // Fetch all attendance records for the date range
        $attendances = ManualAttendance::whereBetween('attendance_date', [
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        ])
        ->when($request->staff_member, function($query) use ($request) {
            return $query->where('person_id', $request->staff_member);
        })
        ->get();

        // Create attendance map with proper initialization
        $attendanceMap = [];
        foreach ($dates as $date) {
            $attendanceMap[$date] = [];
            foreach ($staff as $member) {
                $attendanceMap[$date][$member->id] = null;
            }
        }

        // Fill in the attendance data
        foreach ($attendances as $attendance) {
            $date = $attendance->attendance_date->format('Y-m-d');
            if (isset($attendanceMap[$date])) {
                $attendanceMap[$date][$attendance->person_id] = $attendance;
            }
        }

        // Calculate summary
        $summary = [
            'total_present' => $attendances->where('status', 'present')->count(),
            'total_half' => $attendances->where('status', 'half')->count(),
            'total_absent' => $attendances->where('status', 'absent')->count()
        ];

        return view('attendance.manual.report', compact(
            'staff',
            'dates',
            'attendanceMap',
            'startDate',
            'endDate',
            'summary'
        ));
    }
}