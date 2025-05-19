<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManualAttendance;
use App\Models\Person;
use App\Models\StaffCode;
use App\Models\StaffCategory; // New model for staff categories
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ManualAttendanceController extends Controller
{
   public function index()
    {
        // Get staff members who have staff codes using relationship
        $staffQuery = Person::whereHas('staffCode', function($query) {
                $query->where('is_active', 1);
            })
            ->where('type', 'individual')
            ->with('staffCategory'); // Eager load staff categories
        
        // Get all staff members
        $allStaff = $staffQuery->get();
        
        // Define the order of categories
        $categoryOrder = [
            'front_office',  // Front Office first
            'kitchen',
            'restaurant',
            'maintenance',
            'garden',
            null, // Uncategorized staff will be at the end
        ];
        
        // Group staff by category
        $staffByCategory = [];
        foreach ($categoryOrder as $category) {
            $staffByCategory[$category] = $allStaff->filter(function($staff) use ($category) {
                if ($category === null) {
                    // For null category, get staff with no category
                    return $staff->staffCategory === null;
                }
                return $staff->staffCategory && $staff->staffCategory->category === $category;
            })->sortBy('name')->values();
        }
        
        // Combine all staff in ordered categories
        $staff = collect();
        foreach ($staffByCategory as $categoryStaff) {
            $staff = $staff->concat($categoryStaff);
        }
        
        $today = Carbon::now()->format('Y-m-d');
        
        // Use attendance_date instead of created_at for accurate date retrieval
        $attendances = ManualAttendance::whereDate('attendance_date', $today)
                ->with('person')
                ->get()
                ->keyBy('person_id');
    
        // Add debug logging
        \Log::info('Staff count: ' . $staff->count());
        \Log::info('Staff by category count: ', collect($staffByCategory)->map->count()->toArray());
        \Log::info('Today\'s attendance count: ' . $attendances->count());
    
        // Define category names for display
        $categoryNames = [
            'front_office' => 'Front Office',
            'kitchen' => 'Kitchen',
            'restaurant' => 'Restaurant',
            'maintenance' => 'Maintenance',
            'garden' => 'Garden',
            null => 'Not Assigned'
        ];
    
        return view('attendance.manual.index', compact('staff', 'attendances', 'categoryNames', 'staffByCategory'));
    }
    public function markAttendance(Request $request)
    {
        $request->validate([
            'person_id' => 'required|exists:persons,id',
            'status' => 'required|in:present,half,absent',
            'remarks' => 'nullable|string|max:255',
            'attendance_date' => 'required|date'
        ]);

        // Debug log to check input data
        \Log::info('Marking attendance', [
            'person_id' => $request->person_id,
            'status' => $request->status,
            'date' => $request->attendance_date,
            'now' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        // FIXED: Ensure we're using the app's timezone consistently
        $markDate = Carbon::parse($request->attendance_date, config('app.timezone'))->startOfDay();
        $now = Carbon::now(config('app.timezone'));
        
        // Only admin can mark attendance for previous dates
        if (!Auth::user()->checkAdmin() && $markDate->format('Y-m-d') !== $now->format('Y-m-d')) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can mark attendance for previous dates'
            ], 403);
        }

        // Prevent marking attendance for future dates
        if ($markDate->isAfter($now)) {
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

        // FIXED: Use consistent timezone
        $markDate = Carbon::parse($request->attendance_date, config('app.timezone'))->startOfDay();
        $now = Carbon::now(config('app.timezone'));
        
        // Debug log to track toggle actions
        \Log::info('Toggling attendance', [
            'person_id' => $request->person_id,
            'date' => $markDate->format('Y-m-d'),
            'current_time' => $now->format('Y-m-d H:i:s')
        ]);
        
        // Only admin can mark attendance for previous dates
        if (!Auth::user()->checkAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can toggle attendance for previous dates'
            ], 403);
        }

        // Prevent marking attendance for future dates
        if ($markDate->isAfter($now)) {
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
        
        // FIXED: Improved query to ensure we're getting all attendance records correctly
        $attendances = ManualAttendance::where('person_id', $personId)
            ->whereBetween('attendance_date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ])
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->attendance_date)->format('Y-m-d');
            });
        
        // Debug for troubleshooting
        \Log::info("Staff attendance history for person {$personId}", [
            'month' => $month,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'records_found' => $attendances->count(),
            'dates' => $attendances->pluck('attendance_date', 'status')->toArray()
        ]);
        
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

    public function showManageCategories()
    {
        // Get all active staff members
        $staff = Person::whereHas('staffCode', function($query) {
                $query->where('is_active', 1);
            })
            ->where('type', 'individual')
            ->orderBy('name')
            ->with('staffCategory') // Eager load staff categories
            ->get();

        // Available categories
        $categories = [
            'front_office' => 'Front Office',
            'garden' => 'Garden',
            'kitchen' => 'Kitchen',
            'maintenance' => 'Maintenance',
            'restaurant' => 'Restaurant'
        ];
        
        return view('attendance.manual.manage-categories', compact('staff', 'categories'));
    }


    public function addStaffMember(Request $request)
    {
        $request->validate([
            'person_id' => 'required|exists:persons,id',
            'staff_code' => 'required|string|max:20',
            'staff_category' => 'required|string|in:front_office,garden,kitchen,maintenance,restaurant',
        ]);

        // Only admin can add staff members
        if (!Auth::user()->checkAdmin()) {
            return redirect()->back()->with('error', 'Only administrators can add staff members');
        }

        // Start a transaction to ensure both staff code and category are saved
        DB::beginTransaction();
        
        try {
            // Check if person is already a staff member
            $existingStaff = StaffCode::where('person_id', $request->person_id)->first();
            
            if ($existingStaff) {
                // Update existing staff code
                $existingStaff->staff_code = $request->staff_code;
                $existingStaff->is_active = $request->has('is_active') ? 1 : 0;
                $existingStaff->save();
                
                // Update or create staff category
                StaffCategory::updateOrCreate(
                    ['person_id' => $request->person_id],
                    ['category' => $request->staff_category]
                );
            } else {
                // Create new staff code
                $staffCode = new StaffCode();
                $staffCode->person_id = $request->person_id;
                $staffCode->staff_code = $request->staff_code;
                $staffCode->is_active = $request->has('is_active') ? 1 : 0;
                $staffCode->save();
                
                // Create new staff category
                $staffCategory = new StaffCategory();
                $staffCategory->person_id = $request->person_id;
                $staffCategory->category = $request->staff_category;
                $staffCategory->save();
            }
            
            DB::commit();
            return redirect()->route('attendance.manual.index')->with('success', 'Staff member added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error adding staff member: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error adding staff member: ' . $e->getMessage())->withInput();
        }
    }
    public function updateStaffCategory(Request $request)
    {
        $request->validate([
            'person_id' => 'required|exists:persons,id',
            'category' => 'required|string|in:front_office,garden,kitchen,maintenance,restaurant',
        ]);

        // Only admin can update staff categories
        if (!Auth::user()->checkAdmin()) {
            return redirect()->back()->with('error', 'Only administrators can update staff categories');
        }

        try {
            // Update or create staff category
            StaffCategory::updateOrCreate(
                ['person_id' => $request->person_id],
                ['category' => $request->category]
            );

            return redirect()->back()->with('success', 'Staff category updated successfully');
        } catch (\Exception $e) {
            \Log::error('Error updating staff category: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating staff category: ' . $e->getMessage());
        }
    }

    public function bulkUpdateCategories(Request $request)
{
    // Basic validation
    $request->validate([
        'categories' => 'required|array'
    ]);

    // Only admin can update categories
    if (!Auth::user()->checkAdmin()) {
        return redirect()->back()->with('error', 'Only administrators can update staff categories');
    }

    $successCount = 0;
    $errors = [];

    // Process each category update individually (no transaction)
    foreach ($request->categories as $personId => $category) {
        // Skip if no category selected
        if (empty($category)) {
            continue;
        }

        try {
            // Check if the person exists
            $person = Person::find($personId);
            if (!$person) {
                $errors[] = "Person ID $personId not found";
                continue;
            }

            // Direct database insert/update using query builder
            // This bypasses Eloquent models which could be causing issues
            $exists = DB::table('staff_categories')
                ->where('person_id', $personId)
                ->exists();

            if ($exists) {
                // Update existing record
                DB::table('staff_categories')
                    ->where('person_id', $personId)
                    ->update([
                        'category' => $category,
                        'updated_at' => now()
                    ]);
            } else {
                // Insert new record
                DB::table('staff_categories')
                    ->insert([
                        'person_id' => $personId,
                        'category' => $category,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
            }

            $successCount++;
        } catch (\Exception $e) {
            \Log::error("Error updating category for person $personId: " . $e->getMessage());
            $errors[] = "Could not update category for " . ($person->name ?? "person #$personId") . ": " . $e->getMessage();
        }
    }

    // Prepare response message
    $message = "Updated $successCount staff categories successfully.";
    
    if (count($errors) > 0) {
        $message .= " However, there were " . count($errors) . " errors: " . implode("; ", $errors);
        return redirect()->back()->with('warning', $message);
    }

    return redirect()->back()->with('success', $message);
}

    /**
     * Bulk update staff categories
     */

    
    
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
        
        // Debug log for report date range
        \Log::info('Attendance report date range', [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'staff_count' => $staff->count()
        ]);
        
        // Generate all dates in the range
        $dates = [];
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dates[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        // FIXED: Improved query to ensure we're getting all attendance records correctly by date range
        $attendances = ManualAttendance::whereBetween('attendance_date', [
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        ])
        ->when($request->staff_member, function($query) use ($request) {
            return $query->where('person_id', $request->staff_member);
        })
        ->when($request->staff_category, function($query) use ($request) {
            return $query->whereHas('person.staffCategory', function($q) use ($request) {
                $q->where('category', $request->staff_category);
            });
        })
        ->get();

        // Debug log for report data
        \Log::info('Attendance records found', [
            'total_records' => $attendances->count(),
            'date_range_days' => count($dates),
            'staff_member_filter' => $request->staff_member,
            'staff_category_filter' => $request->staff_category
        ]);

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

        // Get list of categories for filter dropdown
        $categories = [
            'front_office' => 'Front Office',
            'garden' => 'Garden',
            'kitchen' => 'Kitchen',
            'maintenance' => 'Maintenance',
            'restaurant' => 'Restaurant'
        ];

        return view('attendance.manual.report', compact(
            'staff',
            'dates',
            'attendanceMap',
            'startDate',
            'endDate',
            'summary',
            'categories'
        ));
    }

    
}