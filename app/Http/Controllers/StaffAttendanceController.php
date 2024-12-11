<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Imports\AttendanceImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class StaffAttendanceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $staff = Staff::where('status', 'active')->orderBy('staff_code')->get();
            
            $selectedDate = $request->month 
                ? Carbon::createFromFormat('Y-m', $request->month)
                : Carbon::now();
            
            $attendances = Attendance::whereMonth('date', $selectedDate->month)
                ->whereYear('date', $selectedDate->year)
                ->get()
                ->groupBy(function($item) {
                    return $item->staff_id . '-' . Carbon::parse($item->date)->format('d');
                });
        
            Log::info('Loading attendance index', [
                'staff_count' => $staff->count(),
                'attendance_records' => $attendances->count(),
                'selected_month' => $selectedDate->format('Y-m'),
                'session_id' => session()->getId()
            ]);
        
            return view('staff.attendance.index', compact(
                'staff', 
                'attendances',
                'selectedDate'
            ));
        } catch (\Exception $e) {
            Log::error('Error in attendance index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error loading attendance data');
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'staff_id' => 'required|exists:staff,id',
                'check_in' => 'required|date_format:H:i',
                'date' => 'required|date'
            ]);

            $attendance = Attendance::updateOrCreate(
                [
                    'staff_id' => $validated['staff_id'],
                    'date' => $validated['date']
                ],
                [
                    'check_in' => $validated['check_in'],
                    'status' => $this->calculateStatus($validated['check_in'])
                ]
            );

            Log::info('Attendance stored', ['attendance_id' => $attendance->id]);
            return response()->json(['success' => true, 'attendance' => $attendance]);
        } catch (\Exception $e) {
            Log::error('Error storing attendance', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error storing attendance'], 500);
        }
    }

    public function checkOut(Request $request)
    {
        try {
            $validated = $request->validate([
                'staff_id' => 'required|exists:staff,id',
                'check_out' => 'required|date_format:H:i',
                'date' => 'required|date'
            ]);

            $attendance = Attendance::where('staff_id', $validated['staff_id'])
                ->whereDate('date', $validated['date'])
                ->first();

            if ($attendance) {
                $attendance->update(['check_out' => $validated['check_out']]);
                Log::info('Checkout recorded', ['attendance_id' => $attendance->id]);
            }

            return response()->json(['success' => true, 'attendance' => $attendance]);
        } catch (\Exception $e) {
            Log::error('Error recording checkout', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error recording checkout'], 500);
        }
    }

    private function calculateStatus($checkInTime)
    {
        $startTime = Carbon::createFromTimeString('08:30:00');
        $checkIn = Carbon::createFromTimeString($checkInTime);
        return $checkIn->gt($startTime) ? 'late' : 'present';
    }

    public function report(Request $request)
    {
        try {
            $startDate = $request->start_date 
                ? Carbon::parse($request->start_date)
                : Carbon::now()->startOfMonth();
            
            $endDate = $request->end_date 
                ? Carbon::parse($request->end_date)
                : Carbon::now()->endOfMonth();
        
            $staff = Staff::where('status', 'active')->get();
            
            $attendances = Attendance::whereBetween('date', [
                    $startDate->format('Y-m-d'),
                    $endDate->format('Y-m-d')
                ])
                ->get()
                ->groupBy('staff_id');
            
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

            Log::info('Report generated', [
                'date_range' => "$startDate to $endDate",
                'staff_count' => count($reportData)
            ]);
        
            return view('staff.attendance.report', compact(
                'reportData', 
                'startDate', 
                'endDate'
            ));
        } catch (\Exception $e) {
            Log::error('Error generating report', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error generating attendance report');
        }
    }

    public function import(Request $request)
    {
        // Start session if not already started
        if (!session()->isStarted()) {
            session()->start();
        }

        Log::info('Starting attendance import', [
            'session_id' => session()->getId(),
            'user_id' => auth()->id()
        ]);

        try {
            // Validate the request
            $validated = $request->validate([
                'attendance_file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
                'month' => 'nullable|date_format:Y-m'
            ]);

            if (!$request->hasFile('attendance_file') || !$request->file('attendance_file')->isValid()) {
                throw new \Exception('Invalid file upload');
            }

            $file = $request->file('attendance_file');
            $month = $validated['month'] ?? Carbon::now()->format('Y-m');

            // Log upload details
            Log::info('Processing attendance file', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'month' => $month
            ]);

            // Store file temporarily
            $tempPath = 'temp/attendance_' . time() . '.' . $file->getClientOriginalExtension();
            Storage::put($tempPath, file_get_contents($file));

            if (!Storage::exists($tempPath)) {
                throw new \Exception('Failed to store uploaded file');
            }

            // Import the file
            Excel::import(new AttendanceImport($month), Storage::path($tempPath));

            // Clean up
            Storage::delete($tempPath);

            Log::info('Attendance import completed successfully');

            return back()->with('success', 'Attendance data imported successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed during import', [
                'errors' => $e->errors()
            ]);
            return back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Error during attendance import', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->with('error', 'Error importing attendance: ' . $e->getMessage())
                ->withInput();
        }
    }
}