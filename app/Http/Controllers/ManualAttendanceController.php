<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManualAttendance;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ManualAttendanceController extends Controller
{
    public function index()
    {
        $staff = Person::where('type', 'individual')->get();
        $today = Carbon::now()->format('Y-m-d');
        
        $attendances = ManualAttendance::whereDate('created_at', $today)
                                ->with('person')
                                ->get()
                                ->keyBy('person_id');

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

        $markDate = Carbon::parse($request->attendance_date);
        if ($markDate->isAfter(Carbon::now()) || 
            (!Auth::user()->checkAdmin() && $markDate->format('Y-m-d') !== Carbon::now()->format('Y-m-d'))) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to mark attendance for this date'
            ], 403);
        }

        $attendance = ManualAttendance::updateOrCreate(
            [
                'person_id' => $request->person_id,
                'created_at' => $markDate->format('Y-m-d'),
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
        $staff = Person::where('type', 'individual')->get();
        
        // Default to current month if no dates selected
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();
        
        // Get all dates in the range
        $dates = collect();
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dates->push($currentDate->copy());
            $currentDate->addDay();
        }
    
        // Base query for attendance records
        $query = ManualAttendance::whereBetween('created_at', [
            $startDate->startOfDay(),
            $endDate->endOfDay()
        ]);
    
        // Filter by staff member if selected
        if ($request->staff_member && $request->staff_member !== 'All Staff') {
            $query->where('person_id', $request->staff_member);
            $staff = $staff->where('id', $request->staff_member);
        }
    
        // Get attendance records
        $attendances = $query->with(['person', 'markedBy'])
            ->get()
            ->groupBy(function($attendance) {
                return $attendance->created_at->format('Y-m-d');
            });
    
        // Calculate summary for filtered data
        $summary = [
            'total_present' => $attendances->flatten()->where('status', 'present')->count(),
            'total_half' => $attendances->flatten()->where('status', 'half')->count(),
            'total_absent' => $attendances->flatten()->where('status', 'absent')->count(),
        ];
    
        // If staff member is selected, only show dates with records
        if ($request->staff_member && $request->staff_member !== 'All Staff') {
            $recordDates = $attendances->keys()->map(function($date) {
                return Carbon::parse($date);
            });
            $dates = $recordDates;
        }
    
        $selectedStaffMember = $request->staff_member;
    
        return view('attendance.manual.report', compact(
            'staff', 
            'attendances', 
            'startDate', 
            'endDate', 
            'summary',
            'dates',
            'selectedStaffMember'
        ));
    }
}