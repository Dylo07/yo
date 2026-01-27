<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\CategoryType;
use App\Models\StaffAllocation;
use App\Models\LeaveRequest;
use App\Models\FunctionAssignment;
use App\Models\Task;
use App\Models\Sale;
use App\Models\Cost;
use App\Models\Group;
use App\Models\VehicleSecurity;
use App\Models\StockLog;
use App\Models\Inventory;
use App\Models\InStock;
use App\Models\Menu;
use Carbon\Carbon;
use DB;

class StaffAllocationController extends Controller
{
    /**
     * Display the staff allocation dashboard
     */
    public function index()
    {
        // Get staff members who have active staff codes
        $allStaff = Person::whereHas('staffCode', function ($query) {
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
            $staffByCategory[$categoryKey] = $allStaff->filter(function ($staff) use ($category) {
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
        $allStaff = Person::whereHas('staffCode', function ($query) {
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
            $staffByCategory[$categoryKey] = $allStaff->filter(function ($staff) use ($category) {
                if ($category === null) {
                    return $staff->staffCategory === null;
                }
                return $staff->staffCategory && strtolower($staff->staffCategory->category) === strtolower($category);
            })->sortBy('name')->values()->map(function ($staff) {
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
        $staffByCategory = array_filter($staffByCategory, function ($staff) {
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
            ->map(function ($leave) {
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
            ->map(function ($allocation) {
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
            ->map(function ($assignment) {
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
            ->map(function ($group) {
                return $group->map(function ($assignment) {
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

    /**
     * Show the task assignment page for a specific date
     */
    public function assignTasks(Request $request)
    {
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));

        // Get staff allocations for this date
        $allocations = StaffAllocation::where('allocation_date', $date)
            ->with('person')
            ->get();

        // Get function assignments for this date
        $functionAssignments = FunctionAssignment::with(['person', 'booking'])
            ->get()
            ->groupBy('booking_id');

        // Get staff on leave for this date
        $staffOnLeave = LeaveRequest::where('status', 'approved')
            ->where(function ($query) use ($date) {
                $query->whereDate('start_date', '<=', $date)
                    ->whereDate('end_date', '>=', $date);
            })
            ->with('person')
            ->get();

        return view('staff-allocation.assign-tasks', compact('date', 'allocations', 'functionAssignments', 'staffOnLeave'));
    }

    /**
     * Get tasks for a specific date
     */
    public function getTasks(Request $request)
    {
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));

        $tasks = Task::where('start_date', $date)
            ->with('assignedPerson')
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'person_id' => $task->assigned_to,
                    'person_name' => $task->assignedPerson ? $task->assignedPerson->name : 'Unknown',
                    'task' => $task->task,
                ];
            });

        return response()->json(['tasks' => $tasks]);
    }

    /**
     * Save a new task
     */
    public function saveTask(Request $request)
    {
        $validated = $request->validate([
            'person_id' => 'required|exists:persons,id',
            'task' => 'required|string',
            'date' => 'required|date',
        ]);

        $task = Task::create([
            'user' => auth()->user()->name,
            'date_added' => Carbon::today(),
            'start_date' => $validated['date'],
            'end_date' => $validated['date'],
            'task' => $validated['task'],
            'task_category_id' => 1,
            'assigned_to' => $validated['person_id'],
            'person_incharge' => 'Duty Roster',
            'priority_order' => 'Medium',
            'is_done' => false,
        ]);

        return response()->json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'person_id' => $task->assigned_to,
                'task' => $task->task,
            ],
        ]);
    }

    /**
     * Delete a task
     */
    public function deleteTask($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Get bills report for a specific date (00:00 to 23:59 - always full day)
     */
    public function getTodayBills(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));

        $dateStart = Carbon::parse($date)->startOfDay();
        $dateEnd = Carbon::parse($date)->endOfDay();

        $sales = Sale::whereBetween('updated_at', [$dateStart, $dateEnd])
            ->where('sale_status', 'paid')
            ->with('saleDetails')
            ->orderBy('id', 'asc')
            ->get();

        // total_recieved is service charge only, so total = total_price + total_recieved
        $totalSale = $sales->sum(function ($sale) {
            return $sale->total_price + ($sale->total_recieved ?? 0);
        });
        $serviceCharge = $sales->sum('total_recieved');
        $totalBills = $sales->count();

        // Get hourly breakdown
        $hourlyBreakdown = Sale::whereBetween('updated_at', [$dateStart, $dateEnd])
            ->where('sale_status', 'paid')
            ->select(
                DB::raw('HOUR(updated_at) as hour'),
                DB::raw('COUNT(*) as bill_count'),
                DB::raw('SUM(total_price + COALESCE(total_recieved, 0)) as total_amount')
            )
            ->groupBy('hour')
            ->orderBy('hour', 'asc')
            ->get();

        // Get top selling items today
        $topItems = DB::table('sale_details')
            ->join('sales', 'sales.id', '=', 'sale_details.sale_id')
            ->join('menus', 'menus.id', '=', 'sale_details.menu_id')
            ->whereBetween('sales.updated_at', [$dateStart, $dateEnd])
            ->where('sales.sale_status', 'paid')
            ->select(
                'sale_details.menu_name',
                DB::raw('SUM(sale_details.quantity) as total_qty'),
                DB::raw('SUM(sale_details.menu_price * sale_details.quantity) as total_revenue')
            )
            ->groupBy('sale_details.menu_id', 'sale_details.menu_name')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get();

        // Get all bills ordered by ID (same as report) - no limit
        $recentBills = Sale::whereBetween('updated_at', [$dateStart, $dateEnd])
            ->where('sale_status', 'paid')
            ->with('saleDetails')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_sale' => $totalSale,
                'service_charge' => $serviceCharge,
                'total_bills' => $totalBills,
                'hourly_breakdown' => $hourlyBreakdown,
                'top_items' => $topItems,
                'recent_bills' => $recentBills,
                'period_start' => $dateStart->format('Y-m-d H:i:s'),
                'period_end' => $dateEnd->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Get daily costs report for a specific date
     */
    public function getDailyCosts(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));

        $dateStart = Carbon::parse($date)->startOfDay();
        $dateEnd = Carbon::parse($date)->endOfDay();

        // Get costs for the selected date
        $costs = Cost::with(['group', 'person', 'user'])
            ->whereBetween('cost_date', [$dateStart, $dateEnd])
            ->orderBy('cost_date', 'desc')
            ->get();

        $totalCosts = $costs->sum('amount');
        $totalTransactions = $costs->count();

        // Group by category
        $categoryBreakdown = $costs
            ->groupBy('group.name')
            ->map(function ($costs) {
                return [
                    'name' => $costs->first()->group->name ?? 'N/A',
                    'total' => $costs->sum('amount'),
                    'count' => $costs->count(),
                    'items' => $costs->map(function ($cost) {
                        return [
                            'id' => $cost->id,
                            'person' => $cost->person->name ?? 'N/A',
                            'amount' => $cost->amount,
                            'description' => $cost->description,
                            'time' => $cost->created_at->format('H:i'),
                            'user' => $cost->user->name ?? 'N/A'
                        ];
                    })->values()
                ];
            })
            ->sortByDesc(function ($category) {
                return $category['total'];
            })
            ->values();

        // Get recent costs (last 10)
        $recentCosts = $costs->take(10)->map(function ($cost) {
            return [
                'id' => $cost->id,
                'category' => $cost->group->name ?? 'N/A',
                'person' => $cost->person->name ?? 'N/A',
                'amount' => $cost->amount,
                'description' => $cost->description,
                'time' => $cost->created_at->format('H:i'),
                'user' => $cost->user->name ?? 'N/A'
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'total_costs' => $totalCosts,
                'total_transactions' => $totalTransactions,
                'category_breakdown' => $categoryBreakdown,
                'recent_costs' => $recentCosts,
                'date' => $date,
            ]
        ]);
    }

    /**
     * Get daily inventory changes report for a specific date
     */
    public function getDailyInventoryChanges(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));

        $inventoryChanges = StockLog::with(['user', 'item.group'])
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get current stock levels for items with changes today
        $itemIds = $inventoryChanges->pluck('item_id')->unique();
        $currentDate = Carbon::today()->format('Y-m-d');
        $currentStockLevels = [];

        if ($itemIds->count() > 0) {
            $inventoryRecords = Inventory::whereIn('item_id', $itemIds)
                ->where('stock_date', '<=', $currentDate)
                ->orderBy('stock_date', 'desc')
                ->get()
                ->groupBy('item_id');

            foreach ($itemIds as $itemId) {
                if (isset($inventoryRecords[$itemId]) && $inventoryRecords[$itemId]->count() > 0) {
                    $currentStockLevels[$itemId] = $inventoryRecords[$itemId]->first()->stock_level;
                } else {
                    $currentStockLevels[$itemId] = 0;
                }
            }
        }

        // Format data for frontend
        $formattedChanges = $inventoryChanges->map(function ($log) use ($currentStockLevels) {
            $currentStock = $currentStockLevels[$log->item_id] ?? 0;
            $isAdd = $log->action === 'add';

            // Calculate cost (handle null cost_per_unit safely)
            $costPerUnit = $log->item->kitchen_cost_per_unit ?? 0;
            $itemCost = ($costPerUnit > 0) ? ($log->quantity * $costPerUnit) : 0;

            return [
                'id' => $log->id,
                'time' => $log->created_at->format('H:i'),
                'item_name' => $log->item->name ?? 'Unknown Item',
                'category' => $log->item->group->name ?? 'Other',
                'action' => $log->action, // 'added', 'removed', 'updated'
                'location' => $log->location ?? 'Main Kitchen',
                'quantity' => $log->quantity, // Always positive in DB
                'current_stock' => $currentStock,
                'user' => $log->user->name ?? 'Unknown',
                'description' => $log->description,
                'type' => $isAdd ? 'added' : 'removed',
                'cost_per_unit' => $costPerUnit,
                'cost' => $itemCost
            ];
        });

        // Calculate summary stats
        $totalChanges = $formattedChanges->count();
        $itemsAdded = $formattedChanges->where('type', 'added')->count();
        $itemsRemoved = $formattedChanges->where('type', 'removed')->count();

        // Group by category for frontend
        $groupedChanges = $formattedChanges->groupBy('category')->map(function ($items, $category) {
            return [
                'name' => $category,
                'count' => $items->count(),
                'items' => $items->values()
            ];
        })->values(); // Convert to array list

        return response()->json([
            'success' => true,
            'data' => [
                'changes' => $formattedChanges, // Keep flat list for backward compat or flexible use
                'grouped_changes' => $groupedChanges, // New grouped structure
                'summary' => [
                    'total_changes' => $totalChanges,
                    'items_added' => $itemsAdded,
                    'items_removed' => $itemsRemoved
                ],
                'date' => $date,
            ]
        ]);
    }

    /**
     * Get daily water bottle summary for a specific date
     */
    public function getDailyWaterBottleSummary(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $waterBottleMenuId = 2817;

        $waterBottleHistory = InStock::where('menu_id', $waterBottleMenuId)
            ->whereDate('created_at', $date)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $waterBottle = Menu::find($waterBottleMenuId);
        $currentStock = $waterBottle ? $waterBottle->stock : 0;

        $issued = abs($waterBottleHistory->where('stock', '<', 0)->sum('stock'));
        $added = $waterBottleHistory->where('stock', '>', 0)->sum('stock');
        $netChange = $added - $issued;

        $history = $waterBottleHistory->map(function ($record) {
            return [
                'id' => $record->id,
                'time' => $record->created_at->format('H:i'),
                'quantity' => $record->stock,
                'type' => $record->stock > 0 ? 'added' : 'issued',
                'user' => $record->user->name ?? 'Unknown',
                'description' => $record->description,
                'notes' => $record->notes,
                'sale_id' => $record->sale_id
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'current_stock' => $currentStock,
                'issued' => $issued,
                'added' => $added,
                'net_change' => $netChange,
                'history' => $history,
                'date' => $date
            ]
        ]);
    }

    /**
     * Get active orders (unpaid sales) from cashier system
     */
    public function getActiveOrders(Request $request)
    {
        // Get all unpaid sales with their details
        $activeOrders = Sale::where('sale_status', 'unpaid')
            ->with(['saleDetails'])
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedOrders = $activeOrders->map(function ($sale) {
            $itemCount = $sale->saleDetails->sum('quantity');
            $lastUpdated = $sale->updated_at->diffForHumans();

            return [
                'id' => $sale->id,
                'table_name' => $sale->table_name,
                'table_id' => $sale->table_id,
                'total_price' => $sale->total_price,
                'item_count' => $itemCount,
                'user_name' => $sale->user_name,
                'created_at' => $sale->created_at->format('Y-m-d H:i'),
                'updated_at' => $sale->updated_at->format('Y-m-d H:i'),
                'last_updated' => $lastUpdated,
                'items' => $sale->saleDetails->map(function ($detail) {
                    return [
                        'menu_name' => $detail->menu_name,
                        'quantity' => $detail->quantity,
                        'price' => $detail->menu_price,
                        'total' => $detail->menu_price * $detail->quantity,
                        'status' => $detail->status
                    ];
                })
            ];
        });

        $totalOrders = $activeOrders->count();
        $totalAmount = $activeOrders->sum('total_price');
        $totalItems = $activeOrders->sum(function ($sale) {
            return $sale->saleDetails->sum('quantity');
        });

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $formattedOrders,
                'summary' => [
                    'total_orders' => $totalOrders,
                    'total_amount' => $totalAmount,
                    'total_items' => $totalItems
                ]
            ]
        ]);
    }

    /**
     * Get daily attendance summary for a specific date
     */
    public function getDailyAttendanceSummary(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));

        // Get all active staff members
        $allStaff = Person::whereHas('staffCode', function ($query) {
            $query->where('is_active', 1);
        })
            ->where('type', 'individual')
            ->with('staffCategory')
            ->get();

        $totalStaff = $allStaff->count();

        // Get attendance records for this date
        $attendances = \App\Models\ManualAttendance::whereDate('attendance_date', $date)
            ->with('person.staffCategory')
            ->get()
            ->keyBy('person_id');

        // Get staff on leave for this date
        $leaveRequests = LeaveRequest::where('status', 'approved')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->with('person')
            ->get();

        $staffOnLeaveIds = $leaveRequests->pluck('person_id')->toArray();

        // Calculate counts
        $presentCount = $attendances->where('status', 'present')->count();
        $halfDayCount = $attendances->where('status', 'half')->count();
        $absentCount = $attendances->where('status', 'absent')->count();
        $onLeaveCount = count($staffOnLeaveIds);

        // Staff who haven't been marked yet (excluding those on leave)
        $markedStaffIds = $attendances->pluck('person_id')->toArray();
        $notMarkedCount = $allStaff->filter(function ($staff) use ($markedStaffIds, $staffOnLeaveIds) {
            return !in_array($staff->id, $markedStaffIds) && !in_array($staff->id, $staffOnLeaveIds);
        })->count();

        // Calculate attendance rate (present + 0.5*half) / (total - on leave)
        $workingStaff = $totalStaff - $onLeaveCount;
        $attendanceRate = $workingStaff > 0
            ? round(($presentCount + ($halfDayCount * 0.5)) / $workingStaff * 100, 1)
            : 0;

        // Group by category for breakdown
        $categoryBreakdown = [];
        $categoryTypes = CategoryType::getActiveCategories();

        foreach ($categoryTypes as $category) {
            $categoryStaff = $allStaff->filter(function ($staff) use ($category) {
                return $staff->staffCategory && strtolower($staff->staffCategory->category) === strtolower($category->slug);
            });

            $categoryTotal = $categoryStaff->count();
            if ($categoryTotal === 0)
                continue;

            $categoryAttendances = $attendances->filter(function ($att) use ($categoryStaff) {
                return $categoryStaff->contains('id', $att->person_id);
            });

            $categoryBreakdown[] = [
                'name' => $category->name,
                'slug' => $category->slug,
                'total' => $categoryTotal,
                'present' => $categoryAttendances->where('status', 'present')->count(),
                'half' => $categoryAttendances->where('status', 'half')->count(),
                'absent' => $categoryAttendances->where('status', 'absent')->count(),
            ];
        }

        // Get detailed list of staff on leave
        $staffOnLeave = $leaveRequests->map(function ($leave) {
            return [
                'person_id' => $leave->person_id,
                'person_name' => $leave->person ? $leave->person->name : 'Unknown',
                'leave_type' => $leave->leave_type,
                'reason' => $leave->reason,
            ];
        });

        // Get list of absent staff
        $absentStaff = $attendances->where('status', 'absent')->map(function ($att) {
            return [
                'person_id' => $att->person_id,
                'person_name' => $att->person ? $att->person->name : 'Unknown',
                'category' => $att->person && $att->person->staffCategory
                    ? $att->person->staffCategory->category
                    : 'uncategorized',
                'remarks' => $att->remarks,
            ];
        })->values();

        // Get list of not marked staff (for quick marking)
        $notMarkedStaff = $allStaff->filter(function ($staff) use ($markedStaffIds, $staffOnLeaveIds) {
            return !in_array($staff->id, $markedStaffIds) && !in_array($staff->id, $staffOnLeaveIds);
        })->map(function ($staff) {
            return [
                'person_id' => $staff->id,
                'person_name' => $staff->name,
                'category' => $staff->staffCategory ? $staff->staffCategory->category : 'uncategorized',
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date,
                'total_staff' => $totalStaff,
                'present' => $presentCount,
                'half_day' => $halfDayCount,
                'absent' => $absentCount,
                'on_leave' => $onLeaveCount,
                'not_marked' => $notMarkedCount,
                'attendance_rate' => $attendanceRate,
                'category_breakdown' => $categoryBreakdown,
                'staff_on_leave' => $staffOnLeave,
                'absent_staff' => $absentStaff,
                'not_marked_staff' => $notMarkedStaff,
            ]
        ]);
    }

    /**
     * Get daily financial summary (Net Profit/Loss)
     */
    public function getDailyFinancialSummary(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $dateStart = Carbon::parse($date)->startOfDay();
        $dateEnd = Carbon::parse($date)->endOfDay();

        // 1. Income (Sales + Service Charge)
        $sales = Sale::whereBetween('updated_at', [$dateStart, $dateEnd])
            ->where('sale_status', 'paid')
            ->get();

        $totalSales = $sales->sum('total_price');
        $totalServiceCharge = $sales->sum('total_recieved'); // Service charge stored here
        $totalIncome = $totalSales + $totalServiceCharge;

        // 2. Expenses (Daily Costs excluding MD withdrawals)
        // MD person_id is 4 (Owner Withdrawals)
        $expenses = Cost::whereBetween('cost_date', [$dateStart, $dateEnd])
            ->where('person_id', '!=', 4)
            ->sum('amount');

        // 3. Staff Costs (Daily Salary Estimate)
        // Logic: Sum of (Basic Salary / 30) for all active staff
        $activeStaff = Person::whereHas('staffCode', function ($query) {
            $query->where('is_active', 1);
        })
            ->whereNotNull('basic_salary')
            ->get();

        $dailyStaffCost = $activeStaff->sum(function ($staff) {
            return $staff->basic_salary > 0 ? ($staff->basic_salary / 30) : 0;
        });

        // 4. Inventory Costs (COGS)
        // Logic: Value of items consumed/removed today
        // Actions: 'add' = stock added, anything else (remove_main_kitchen, remove_banquet_hall, etc.) = stock consumed

        $stockLogs = \App\Models\StockLog::with('item')
            ->whereBetween('created_at', [$dateStart, $dateEnd])
            ->get();

        // Filter for removal actions (anything that is NOT 'add')
        $cogs = $stockLogs->filter(function ($log) {
            // All actions except 'add' are consumption/removal
            return $log->action !== 'add';
        })->sum(function ($log) {
            // Calculate cost: quantity * cost_per_unit
            $costPerUnit = $log->item->kitchen_cost_per_unit ?? 0;
            return $log->quantity * $costPerUnit;
        });

        // Calculate Net Profit
        $totalExpenses = $expenses + $dailyStaffCost + $cogs;
        $netProfit = $totalIncome - $totalExpenses;

        $status = $netProfit >= 0 ? 'profit' : 'loss';
        $margin = $totalIncome > 0 ? ($netProfit / $totalIncome) * 100 : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date,
                'income' => [
                    'total' => $totalIncome,
                    'sales' => $totalSales,
                    'service_charge' => $totalServiceCharge
                ],
                'expenses' => [
                    'total' => $totalExpenses,
                    'operational' => $expenses,
                    'staff_cost' => $dailyStaffCost,
                    'cogs' => $cogs
                ],
                'net_profit' => $netProfit,
                'profit_margin' => round($margin, 1),
                'status' => $status
            ]
        ]);
    }
}
