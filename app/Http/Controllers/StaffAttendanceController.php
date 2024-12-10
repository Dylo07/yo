<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Imports\AttendanceImport;
use Maatwebsite\Excel\Facades\Excel;


class StaffAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $staff = Staff::where('status', 'active')->orderBy('staff_code')->get();
        
        // Get selected month or default to current month
        $selectedDate = $request->month 
            ? Carbon::createFromFormat('Y-m', $request->month)
            : Carbon::now();
        
        // Get attendances for selected month
        $attendances = Attendance::whereMonth('date', $selectedDate->month)
            ->whereYear('date', $selectedDate->year)
            ->get()
            ->groupBy(function($item) {
                return $item->staff_id . '-' . Carbon::parse($item->date)->format('d');
            });
    
        // Add debug information
        \Log::info('Staff count: ' . $staff->count());
        \Log::info('Attendance records: ' . $attendances->count());
    
        return view('staff.attendance.index', compact(
            'staff', 
            'attendances',
            'selectedDate'
        ));
    }
    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'check_in' => 'required|date_format:H:i',
            'date' => 'required|date'
        ]);

        $attendance = Attendance::updateOrCreate(
            [
                'staff_id' => $request->staff_id,
                'date' => $request->date
            ],
            [
                'check_in' => $request->check_in,
                'status' => $this->calculateStatus($request->check_in)
            ]
        );

        return response()->json(['success' => true, 'attendance' => $attendance]);
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'check_out' => 'required|date_format:H:i',
            'date' => 'required|date'
        ]);

        $attendance = Attendance::where('staff_id', $request->staff_id)
            ->whereDate('date', $request->date)
            ->first();

        if ($attendance) {
            $attendance->update([
                'check_out' => $request->check_out
            ]);
        }

        return response()->json(['success' => true, 'attendance' => $attendance]);
    }

    private function calculateStatus($checkInTime)
    {
        $startTime = Carbon::createFromTimeString('08:30:00');
        $checkIn = Carbon::createFromTimeString($checkInTime);

        return $checkIn->gt($startTime) ? 'late' : 'present';
    }

    public function report(Request $request)
    {
        // Format the dates - default to current month if no dates selected
        $startDate = $request->start_date 
            ? Carbon::parse($request->start_date)
            : Carbon::now()->startOfMonth();
        
        $endDate = $request->end_date 
            ? Carbon::parse($request->end_date)
            : Carbon::now()->endOfMonth();
    
        // Get all staff
        $staff = Staff::where('status', 'active')->get();
        
        // Get attendances
        $attendances = Attendance::whereBetween('date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ])
            ->get()
            ->groupBy('staff_id');
        
        // Get total days in the selected period
        $totalDays = $endDate->diffInDays($startDate) + 1;
    
        $reportData = [];
        foreach($staff as $member) {
            $staffAttendances = $attendances->get($member->id, collect([]));
            
            $reportData[$member->id] = [
                'name' => $member->name,
                'total_days' => $totalDays,
                'present' => $staffAttendances->where('status', 'present')->count(),
                'late' => $staffAttendances->where('status', 'late')->count(),
                'absent' => $totalDays - ($staffAttendances->where('status', 'present')->count() + 
                                        $staffAttendances->where('status', 'late')->count())
            ];
        }
    
        return view('staff.attendance.report', compact(
            'reportData', 
            'startDate', 
            'endDate'
        ));
    }


    public function import(Request $request)
{
    $request->validate([
        'attendance_file' => 'required|mimes:xlsx,xls',
        'month' => 'nullable|date_format:Y-m'
    ]);

    try {
        $month = $request->month ?? Carbon::now()->format('Y-m');
        Excel::import(new AttendanceImport($month), $request->file('attendance_file'));
        return back()->with('success', 'Attendance data imported successfully');
    } catch (\Exception $e) {
        return back()->with('error', 'Error importing attendance: ' . $e->getMessage());
    }
}
}