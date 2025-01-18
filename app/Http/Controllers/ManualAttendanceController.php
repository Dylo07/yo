<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManualAttendance;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema; // Add this import

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