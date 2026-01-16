<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\CategoryType;
use App\Models\StaffAllocation;
use App\Models\LeaveRequest;
use App\Models\FunctionAssignment;
use Carbon\Carbon;

class StaffAllocationController extends Controller
{
    /**
     * Display the staff allocation dashboard
     */
    public function index()
    {
        // Get staff members who have active staff codes
        $allStaff = Person::whereHas('staffCode', function($query) {
                $query->where('is_active', 1);
            })
            ->where('type', 'individual')
            ->with('staffCategory')
            ->get();

        // Get dynamic category order from database
        $categoryTypes = CategoryType::getActiveCategories();
        $categoryOrder = $categoryTypes->pluck('slug')->toArray();
        $categoryOrder[] = null; // Uncategorized staff at the end

        // Group staff by category
        $staffByCategory = [];
        foreach ($categoryOrder as $category) {
            $categoryKey = $category ?? 'uncategorized';
            $staffByCategory[$categoryKey] = $allStaff->filter(function($staff) use ($category) {
                if ($category === null) {
                    return $staff->staffCategory === null;
                }
                return $staff->staffCategory && strtolower($staff->staffCategory->category) === strtolower($category);
            })->sortBy('name')->values();
        }

        // Get category names
        $categoryNames = $categoryTypes->pluck('name', 'slug')->toArray();
        $categoryNames['uncategorized'] = 'Not Assigned';

        $staff = $allStaff;
        return view('staff-allocation.index', compact('staff', 'staffByCategory', 'categoryNames'));
    }

    /**
     * Get staff data for the allocation dashboard API
     */
    public function getStaff()
    {
        // Get staff members who have active staff codes
        $allStaff = Person::whereHas('staffCode', function($query) {
                $query->where('is_active', 1);
            })
            ->where('type', 'individual')
            ->with('staffCategory')
            ->get();

        // Get dynamic category order from database
        $categoryTypes = CategoryType::getActiveCategories();
        $categoryOrder = $categoryTypes->pluck('slug')->toArray();
        $categoryOrder[] = null; // Uncategorized staff at the end

        // Group staff by category
        $staffByCategory = [];
        foreach ($categoryOrder as $category) {
            $categoryKey = $category ?? 'uncategorized';
            $staffByCategory[$categoryKey] = $allStaff->filter(function($staff) use ($category) {
                if ($category === null) {
                    return $staff->staffCategory === null;
                }
                return $staff->staffCategory && strtolower($staff->staffCategory->category) === strtolower($category);
            })->sortBy('name')->values()->map(function($staff) {
                return [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'staffCategory' => $staff->staffCategory ? [
                        'category' => $staff->staffCategory->category
                    ] : null
                ];
            })->values();
        }

        // Remove empty categories
        $staffByCategory = array_filter($staffByCategory, function($staff) {
            return count($staff) > 0;
        });

        // Get category names
        $categoryNames = $categoryTypes->pluck('name', 'slug')->toArray();
        $categoryNames['uncategorized'] = 'Not Assigned';

        return response()->json([
            'staffByCategory' => $staffByCategory,
            'categoryNames' => $categoryNames,
        ]);
    }

    /**
     * Get staff on leave for a specific date
     */
    public function getStaffOnLeave(Request $request)
    {
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));
        
        $staffOnLeave = LeaveRequest::with('person')
            ->where('status', 'approved')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->get()
            ->map(function($leave) {
                return [
                    'person_id' => $leave->person_id,
                    'person_name' => $leave->person ? $leave->person->name : 'Unknown',
                    'leave_type' => $leave->leave_type,
                    'reason' => $leave->reason,
                ];
            });

        return response()->json([
            'date' => $date,
            'staffOnLeave' => $staffOnLeave,
        ]);
    }

    /**
     * Save a staff allocation
     */
    public function saveAllocation(Request $request)
    {
        $validated = $request->validate([
            'person_id' => 'required|exists:persons,id',
            'section_id' => 'required|string',
            'section_name' => 'required|string',
            'allocation_date' => 'required|date',
        ]);

        // Update or create the allocation
        $allocation = StaffAllocation::updateOrCreate(
            [
                'person_id' => $validated['person_id'],
                'allocation_date' => $validated['allocation_date'],
            ],
            [
                'section_id' => $validated['section_id'],
                'section_name' => $validated['section_name'],
                'assigned_by' => auth()->id(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Allocation saved successfully',
            'allocation' => $allocation,
        ]);
    }

    /**
     * Remove a staff allocation
     */
    public function removeAllocation(Request $request)
    {
        $validated = $request->validate([
            'person_id' => 'required|exists:persons,id',
            'allocation_date' => 'required|date',
        ]);

        $deleted = StaffAllocation::where('person_id', $validated['person_id'])
            ->where('allocation_date', $validated['allocation_date'])
            ->delete();

        return response()->json([
            'success' => $deleted > 0,
            'message' => $deleted > 0 ? 'Allocation removed successfully' : 'No allocation found',
        ]);
    }

    /**
     * Get all allocations for a specific date
     */
    public function getAllocations(Request $request)
    {
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));

        $allocations = StaffAllocation::where('allocation_date', $date)
            ->with('person')
            ->get()
            ->map(function($allocation) {
                return [
                    'person_id' => $allocation->person_id,
                    'person_name' => $allocation->person ? $allocation->person->name : 'Unknown',
                    'section_id' => $allocation->section_id,
                    'section_name' => $allocation->section_name,
                ];
            });

        return response()->json([
            'date' => $date,
            'allocations' => $allocations,
        ]);
    }

    /**
     * Clear all allocations for a specific date
     */
    public function clearAllocations(Request $request)
    {
        $validated = $request->validate([
            'allocation_date' => 'required|date',
        ]);

        $deleted = StaffAllocation::where('allocation_date', $validated['allocation_date'])->delete();

        return response()->json([
            'success' => true,
            'message' => "Cleared {$deleted} allocations",
            'deleted_count' => $deleted,
        ]);
    }

    /**
     * Assign staff to a function/booking
     */
    public function assignToFunction(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|integer',
            'person_id' => 'required|exists:persons,id',
            'role' => 'nullable|string|max:100',
        ]);

        $assignment = FunctionAssignment::updateOrCreate(
            [
                'booking_id' => $validated['booking_id'],
                'person_id' => $validated['person_id'],
            ],
            [
                'role' => $validated['role'] ?? null,
                'assigned_by' => auth()->id(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Staff assigned to function successfully',
            'assignment' => $assignment,
        ]);
    }

    /**
     * Remove staff from a function/booking
     */
    public function removeFromFunction(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|integer',
            'person_id' => 'required|exists:persons,id',
        ]);

        $deleted = FunctionAssignment::where('booking_id', $validated['booking_id'])
            ->where('person_id', $validated['person_id'])
            ->delete();

        return response()->json([
            'success' => $deleted > 0,
            'message' => $deleted > 0 ? 'Staff removed from function' : 'Assignment not found',
        ]);
    }

    /**
     * Get all staff assigned to a function/booking
     */
    public function getFunctionAssignments(Request $request)
    {
        $bookingId = $request->query('booking_id');
        
        if (!$bookingId) {
            return response()->json(['error' => 'booking_id is required'], 400);
        }

        $assignments = FunctionAssignment::where('booking_id', $bookingId)
            ->with('person')
            ->get()
            ->map(function($assignment) {
                return [
                    'person_id' => $assignment->person_id,
                    'person_name' => $assignment->person ? $assignment->person->name : 'Unknown',
                    'role' => $assignment->role,
                ];
            });

        return response()->json([
            'booking_id' => $bookingId,
            'assignments' => $assignments,
        ]);
    }

    /**
     * Get all function assignments for bookings on a specific date
     */
    public function getAllFunctionAssignments(Request $request)
    {
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));
        
        // Get all function assignments and group by booking_id
        $assignments = FunctionAssignment::with('person')
            ->get()
            ->groupBy('booking_id')
            ->map(function($group) {
                return $group->map(function($assignment) {
                    return [
                        'person_id' => $assignment->person_id,
                        'person_name' => $assignment->person ? $assignment->person->name : 'Unknown',
                        'role' => $assignment->role,
                    ];
                });
            });

        return response()->json([
            'assignments' => $assignments,
        ]);
    }
}
