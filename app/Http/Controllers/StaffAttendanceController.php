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
    public function __construct()
    {
        // Ensure upload directory exists and is writable
        $tempPath = storage_path('app/temp');
        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0755, true);
        }
    }

    public function index(Request $request)
    {
        try {
            $staff = Staff::where('status', 'active')->orderBy('staff_code')->get();
            
            $selectedDate = $request->month 
                ? Carbon::createFromFormat('Y-m', $request->month)
                : Carbon::now();
            
            // Get attendance records and group them properly
            $attendanceRecords = Attendance::whereMonth('date', $selectedDate->month)
                ->whereYear('date', $selectedDate->year)
                ->get();
            
            // Process attendance data to separate IN/OUT times properly
            $attendances = collect();
            
            foreach($attendanceRecords as $record) {
                $dayKey = $record->staff_id . '-' . Carbon::parse($record->date)->format('d');
                
                // Parse the raw punch times - handle both space and other separators
                $rawData = trim($record->raw_data);
                $punchTimes = [];
                
                if (!empty($rawData) && strtolower($rawData) !== 'absent') {
                    // Split by spaces, commas, or other common separators
                    $punchTimes = preg_split('/[\s,;]+/', $rawData);
                    $punchTimes = array_filter($punchTimes, function($time) {
                        $time = trim($time);
                        // Validate time format HH:MM
                        return preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $time);
                    });
                    $punchTimes = array_values($punchTimes); // Re-index array
                }
                
                // Create structured data for each day
                $dayData = [
                    'staff_id' => $record->staff_id,
                    'date' => $record->date,
                    'day' => Carbon::parse($record->date)->format('d'),
                    'in_time' => isset($punchTimes[0]) ? trim($punchTimes[0]) : null,
                    'out_time' => isset($punchTimes[1]) ? trim($punchTimes[1]) : null,
                    'all_punches' => $punchTimes,
                    'raw_data' => $record->raw_data,
                    'status' => $record->status
                ];
                
                $attendances->put($dayKey, collect([$dayData]));
            }
        
            Log::info('Loading attendance index with proper IN/OUT separation', [
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
                'punch_time' => 'required|date_format:H:i',
                'date' => 'required|date',
                'punch_type' => 'required|in:in,out'
            ]);

            // Get existing attendance record for the day
            $attendance = Attendance::firstOrCreate(
                [
                    'staff_id' => $validated['staff_id'],
                    'date' => $validated['date']
                ],
                [
                    'raw_data' => '',
                    'status' => 'present'
                ]
            );

            // Parse existing punch times
            $existingTimes = array_filter(explode(' ', $attendance->raw_data));
            
            // Add new punch time
            $existingTimes[] = $validated['punch_time'];
            
            // Sort times chronologically
            sort($existingTimes);
            
            // Update attendance record
            $attendance->update([
                'raw_data' => implode(' ', $existingTimes),
                'check_in' => $existingTimes[0] ?? null,
                'check_out' => count($existingTimes) > 1 ? end($existingTimes) : null,
                'status' => $this->calculateStatus($existingTimes[0] ?? null)
            ]);

            Log::info('Attendance punch recorded', [
                'attendance_id' => $attendance->id,
                'punch_type' => $validated['punch_type'],
                'total_punches' => count($existingTimes)
            ]);
            
            return response()->json([
                'success' => true, 
                'attendance' => $attendance,
                'punch_count' => count($existingTimes)
            ]);
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
                // Parse existing times
                $existingTimes = array_filter(explode(' ', $attendance->raw_data));
                
                // Add checkout time
                $existingTimes[] = $validated['check_out'];
                
                // Sort times chronologically
                sort($existingTimes);
                
                $attendance->update([
                    'raw_data' => implode(' ', $existingTimes),
                    'check_out' => $validated['check_out']
                ]);
                
                Log::info('Checkout recorded', [
                    'attendance_id' => $attendance->id,
                    'total_punches' => count($existingTimes)
                ]);
            }

            return response()->json(['success' => true, 'attendance' => $attendance]);
        } catch (\Exception $e) {
            Log::error('Error recording checkout', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error recording checkout'], 500);
        }
    }

    private function calculateStatus($checkInTime)
    {
        if (!$checkInTime) return 'absent';
        
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
                
                // Calculate detailed metrics
                $presentDays = $staffAttendances->where('status', 'present')->count();
                $lateDays = $staffAttendances->where('status', 'late')->count();
                $absentDays = $totalDays - ($presentDays + $lateDays);
                
                // Calculate total working hours
                $totalHours = 0;
                foreach($staffAttendances as $attendance) {
                    $times = array_filter(explode(' ', $attendance->raw_data));
                    if(count($times) >= 2) {
                        $checkIn = Carbon::createFromTimeString($times[0]);
                        $checkOut = Carbon::createFromTimeString(end($times));
                        $totalHours += $checkOut->diffInHours($checkIn);
                    }
                }
                
                $reportData[$member->id] = [
                    'name' => $member->name,
                    'staff_code' => $member->staff_code,
                    'total_days' => $totalDays,
                    'present' => $presentDays,
                    'late' => $lateDays,
                    'absent' => $absentDays,
                    'total_hours' => round($totalHours, 1),
                    'average_hours' => $presentDays + $lateDays > 0 ? round($totalHours / ($presentDays + $lateDays), 1) : 0
                ];
            }

            Log::info('Enhanced report generated', [
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
        // Enable error reporting for debugging
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        Log::info('Starting enhanced import process', [
            'request_headers' => $request->headers->all(),
            'session_id' => session()->getId()
        ]);

        try {
            // Validate the incoming request
            $validated = $request->validate([
                'attendance_file' => [
                    'required',
                    'file',
                    'mimes:xlsx,xls',
                    'max:10240' // Increased to 10MB max
                ],
                'month' => 'nullable|date_format:Y-m'
            ]);

            // Check if file exists and is valid
            if (!$request->hasFile('attendance_file') || !$request->file('attendance_file')->isValid()) {
                Log::error('Invalid file upload');
                throw new \Exception('The uploaded file is invalid or corrupted');
            }

            $file = $request->file('attendance_file');
            
            // Log file details
            Log::info('File details', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'error' => $file->getError()
            ]);

            // Generate a unique filename
            $filename = 'attendance_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Store file directly in storage path
            $filePath = storage_path('app/temp/' . $filename);
            
            // Move uploaded file
            if (!move_uploaded_file($file->getPathname(), $filePath)) {
                throw new \Exception('Failed to move uploaded file');
            }

            // Process the Excel file with enhanced import
            $importMonth = $request->month ?? Carbon::now()->format('Y-m');
            Excel::import(
                new AttendanceImport($importMonth),
                $filePath
            );

            // Clean up
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            Log::info('Enhanced import completed successfully', [
                'month' => $importMonth,
                'file_processed' => $filename
            ]);

            // Redirect with success message
            return redirect()
                ->route('staff.attendance.index', ['month' => $importMonth])
                ->with('success', 'Attendance data imported successfully with multiple punch times support');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error', [
                'errors' => $e->errors(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Import error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Import failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get attendance statistics for dashboard
     */
    public function getAttendanceStats(Request $request)
    {
        try {
            $date = $request->date ?? Carbon::today()->format('Y-m-d');
            
            $todayAttendances = Attendance::whereDate('date', $date)->get();
            
            $stats = [
                'total_staff' => Staff::where('status', 'active')->count(),
                'present_today' => $todayAttendances->whereIn('status', ['present', 'late'])->count(),
                'late_today' => $todayAttendances->where('status', 'late')->count(),
                'absent_today' => Staff::where('status', 'active')->count() - $todayAttendances->count(),
                'total_punches' => $todayAttendances->sum(function($attendance) {
                    return count(array_filter(explode(' ', $attendance->raw_data)));
                })
            ];
            
            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error getting attendance stats', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to get stats'], 500);
        }
    }
}