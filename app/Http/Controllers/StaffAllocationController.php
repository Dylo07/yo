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
use App\Models\GatePass;
use App\Models\Booking;
use App\Models\Room;
use App\Models\Item;
use App\Models\Lead;
use App\Models\DamageItem;
use App\Models\ProductGroup;
use App\Models\CustomerFeedback;
use App\Models\User;
use App\Models\DailySalesSummary;
use App\Models\ManualAttendance;
use App\Models\Salary;
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

    /**
     * Get monthly financial summary (Profit/Loss) using DailySalesSummary for income
     * Income = Cash Payment + Card Payment + Bank Payment from daily_sales_summaries
     */
    public function getMonthlyFinancialSummary(Request $request)
    {
        try {
            $month = $request->input('month', date('m'));
            $year = $request->input('year', date('Y'));

            $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();
            $daysInMonth = $startOfMonth->daysInMonth;
            $today = Carbon::today();

            // ---- INCOME from daily_sales_summaries (Cash + Card + Bank) ----
            $dailySummaries = DailySalesSummary::whereBetween('date', [
                $startOfMonth->format('Y-m-d'),
                $endOfMonth->format('Y-m-d')
            ])->get();

            // Group by date
            $incomeByDate = $dailySummaries->groupBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            })->map(function ($dayItems) {
                return [
                    'cash' => $dayItems->sum('cash_payment'),
                    'card' => $dayItems->sum('card_payment'),
                    'bank' => $dayItems->sum('bank_payment'),
                    'total' => $dayItems->sum('cash_payment') + $dayItems->sum('card_payment') + $dayItems->sum('bank_payment'),
                    'bills_count' => $dayItems->count(),
                ];
            });

            $totalCash = $dailySummaries->sum('cash_payment');
            $totalCard = $dailySummaries->sum('card_payment');
            $totalBank = $dailySummaries->sum('bank_payment');
            $totalIncome = $totalCash + $totalCard + $totalBank;

            // ---- EXPENSES (Costs excluding MD withdrawals) ----
            $totalOperational = Cost::whereBetween('cost_date', [$startOfMonth, $endOfMonth])
                ->where('person_id', '!=', 4)
                ->sum('amount');

            // Expenses by date
            $expensesByDate = Cost::whereBetween('cost_date', [$startOfMonth, $endOfMonth])
                ->where('person_id', '!=', 4)
                ->selectRaw('DATE(cost_date) as date, SUM(amount) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            // ---- STAFF COSTS (Daily salary estimate * days elapsed) ----
            $activeStaff = Person::whereHas('staffCode', function ($query) {
                $query->where('is_active', 1);
            })->whereNotNull('basic_salary')->get();

            $dailyStaffCost = $activeStaff->sum(function ($staff) {
                return $staff->basic_salary > 0 ? ($staff->basic_salary / 30) : 0;
            });

            // Calculate days elapsed in this month (up to today or end of month)
            $lastDay = $endOfMonth->lt($today) ? $daysInMonth : $today->day;
            $totalStaffCost = $dailyStaffCost * $lastDay;

            // ---- INVENTORY COGS ----
            $stockLogs = StockLog::with('item')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->get();

            $totalCogs = $stockLogs->filter(function ($log) {
                return $log->action !== 'add';
            })->sum(function ($log) {
                $costPerUnit = $log->item->kitchen_cost_per_unit ?? 0;
                return $log->quantity * $costPerUnit;
            });

            // COGS by date
            $cogsByDate = $stockLogs->filter(function ($log) {
                return $log->action !== 'add';
            })->groupBy(function ($log) {
                return $log->created_at->format('Y-m-d');
            })->map(function ($dayLogs) {
                return $dayLogs->sum(function ($log) {
                    $costPerUnit = $log->item->kitchen_cost_per_unit ?? 0;
                    return $log->quantity * $costPerUnit;
                });
            });

            // ---- CALCULATE TOTALS ----
            $totalExpenses = $totalOperational + $totalStaffCost + $totalCogs;
            $netProfit = $totalIncome - $totalExpenses;
            $status = $netProfit >= 0 ? 'profit' : 'loss';
            $margin = $totalIncome > 0 ? ($netProfit / $totalIncome) * 100 : 0;

            // ---- BUILD DAILY BREAKDOWN ----
            $dailyBreakdown = [];
            for ($d = 1; $d <= $lastDay; $d++) {
                $dateStr = Carbon::createFromDate($year, $month, $d)->format('Y-m-d');
                $dayIncome = $incomeByDate->get($dateStr, ['total' => 0, 'cash' => 0, 'card' => 0, 'bank' => 0, 'bills_count' => 0]);
                $dayExpenses = (float)($expensesByDate[$dateStr] ?? 0);
                $dayCogs = (float)($cogsByDate[$dateStr] ?? 0);
                $dayTotal = $dayIncome['total'] - $dayExpenses - $dailyStaffCost - $dayCogs;

                $dailyBreakdown[] = [
                    'date' => $dateStr,
                    'day' => $d,
                    'income' => $dayIncome['total'],
                    'expenses' => $dayExpenses,
                    'staff_cost' => round($dailyStaffCost, 2),
                    'cogs' => round($dayCogs, 2),
                    'net' => round($dayTotal, 2),
                    'bills_count' => $dayIncome['bills_count'],
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'month' => (int)$month,
                    'year' => (int)$year,
                    'month_name' => $startOfMonth->format('F Y'),
                    'days_counted' => $lastDay,
                    'income' => [
                        'total' => round($totalIncome, 2),
                        'cash' => round($totalCash, 2),
                        'card' => round($totalCard, 2),
                        'bank' => round($totalBank, 2),
                    ],
                    'expenses' => [
                        'total' => round($totalExpenses, 2),
                        'operational' => round($totalOperational, 2),
                        'staff_cost' => round($totalStaffCost, 2),
                        'cogs' => round($totalCogs, 2),
                    ],
                    'net_profit' => round($netProfit, 2),
                    'profit_margin' => round($margin, 1),
                    'status' => $status,
                    'daily_breakdown' => $dailyBreakdown,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Get salary summary for dashboard widget (Admin Only)
     * Mirrors the /salary page logic: basic salary, attendance, advances, final salary
     */
    public function getSalarySummary(Request $request)
    {
        try {
            $month = $request->input('month', date('m'));
            $year = $request->input('year', date('Y'));

            // Get active staff
            $staff = Person::whereHas('staffCode', function ($query) {
                $query->where('is_active', 1);
            })->orderBy('name')->get();

            // Salary advance period: 10th of selected month to 10th of next month
            $periodStart = Carbon::create($year, $month, 10, 0, 0, 0);
            $periodEnd = Carbon::create($year, $month, 1)->addMonth()->setDay(10)->setTime(23, 59, 59);

            // Fetch all salary advances for the period
            $salaryAdvances = Cost::with('person')
                ->where('group_id', 1)
                ->whereBetween('created_at', [$periodStart, $periodEnd])
                ->orderBy('created_at', 'desc')
                ->get();

            $totalAdvance = $salaryAdvances->sum('amount');

            // Get attendance data for all staff
            $allAttendance = ManualAttendance::whereIn('person_id', $staff->pluck('id'))
                ->whereYear('attendance_date', $year)
                ->whereMonth('attendance_date', $month)
                ->get()
                ->groupBy('person_id');

            $lastDayOfMonth = Carbon::create($year, $month)->endOfMonth()->day;

            $totalBasicSalary = 0;
            $totalSalaryAdvance = 0;
            $totalFinalSalary = 0;
            $totalPresentDays = 0;
            $totalAbsentDays = 0;
            $employeeData = [];

            foreach ($staff as $employee) {
                $attendance = $allAttendance->get($employee->id, collect());
                $present = $attendance->where('status', 'present')->count();
                $half = $attendance->where('status', 'half')->count();
                $absent = $attendance->where('status', 'absent')->count();
                $totalMarkedDays = $present + $half + $absent;

                $presentDays = $present + ($half * 0.5);
                $displayAbsentDays = $absent + ($half * 0.5);
                $showAttendance = $totalMarkedDays > 0;

                $empAdvance = $salaryAdvances->where('person_id', $employee->id)->sum('amount');
                $finalSalary = 0;

                if ($employee->basic_salary > 0) {
                    if ($totalMarkedDays < $lastDayOfMonth) {
                        $finalSalary = ($presentDays * $employee->basic_salary / 30) - $empAdvance;
                    } else {
                        $totalDaysOff = $absent + ($half * 0.5);
                        if ($totalDaysOff == 5) {
                            $finalSalary = $employee->basic_salary - $empAdvance;
                        } elseif ($totalDaysOff < 5) {
                            $additionalDays = 5 - $totalDaysOff;
                            $dailyRate = $employee->basic_salary / 30;
                            $finalSalary = $employee->basic_salary - $empAdvance + ($additionalDays * $dailyRate);
                        } else {
                            $excessDays = $totalDaysOff - 5;
                            $dailyRate = $employee->basic_salary / 25;
                            $finalSalary = $employee->basic_salary - $empAdvance - ($excessDays * $dailyRate);
                        }
                    }
                }

                $totalBasicSalary += $employee->basic_salary ?? 0;
                $totalSalaryAdvance += $empAdvance;
                $totalFinalSalary += $finalSalary;
                $totalPresentDays += $showAttendance ? $presentDays : 0;
                $totalAbsentDays += $showAttendance ? $displayAbsentDays : 0;

                $employeeData[] = [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'basic_salary' => round($employee->basic_salary ?? 0, 2),
                    'salary_advance' => round($empAdvance, 2),
                    'present_days' => $showAttendance ? $presentDays : null,
                    'absent_days' => $showAttendance ? $displayAbsentDays : null,
                    'final_salary' => round($finalSalary, 2),
                ];
            }

            // Advances grouped by person
            $advancesByPerson = $salaryAdvances->groupBy(function ($item) {
                return $item->person->name ?? 'Unknown';
            })->map(function ($group) {
                return [
                    'total' => round($group->sum('amount'), 2),
                    'count' => $group->count(),
                ];
            });

            $monthName = Carbon::create($year, $month, 1)->format('F Y');

            return response()->json([
                'success' => true,
                'data' => [
                    'month' => (int)$month,
                    'year' => (int)$year,
                    'month_name' => $monthName,
                    'staff_count' => $staff->count(),
                    'totals' => [
                        'basic_salary' => round($totalBasicSalary, 2),
                        'salary_advance' => round($totalSalaryAdvance, 2),
                        'final_salary' => round($totalFinalSalary, 2),
                        'present_days' => round($totalPresentDays, 1),
                        'absent_days' => round($totalAbsentDays, 1),
                    ],
                    'advance_period' => $periodStart->format('M d') . ' - ' . $periodEnd->format('M d, Y'),
                    'advances_by_person' => $advancesByPerson,
                    'employees' => $employeeData,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Get owner's personal tasks (My Priority List)
     */
    public function getOwnerTasks(Request $request)
    {
        $tasks = Task::where('staff_category', 'owner_personal')
            ->orderBy('is_done', 'asc')
            ->orderBy('priority_order', 'asc')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'task' => $task->task,
                    'priority' => $task->priority_order,
                    'is_done' => (bool) $task->is_done,
                    'created_at' => $task->created_at->format('M d, H:i'),
                    'end_date' => $task->end_date,
                    'is_overdue' => $task->isOverdue(),
                ];
            });

        $stats = [
            'total' => $tasks->count(),
            'pending' => $tasks->where('is_done', false)->count(),
            'completed' => $tasks->where('is_done', true)->count(),
        ];

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
            'stats' => $stats
        ]);
    }

    /**
     * Add owner's personal task
     */
    public function addOwnerTask(Request $request)
    {
        $request->validate([
            'task' => 'required|string|max:500',
            'priority' => 'nullable|in:High,Medium,Low',
        ]);

        $task = Task::create([
            'user' => auth()->user()->name ?? 'Owner',
            'date_added' => now()->format('Y-m-d'),
            'start_date' => now()->format('Y-m-d'),
            'task' => $request->task,
            'task_category_id' => 1, // Default category
            'staff_category' => 'owner_personal',
            'person_incharge' => 'Owner',
            'priority_order' => $request->priority ?? 'Medium',
            'is_done' => false,
        ]);

        return response()->json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'task' => $task->task,
                'priority' => $task->priority_order,
                'is_done' => false,
                'created_at' => $task->created_at->format('M d, H:i'),
            ]
        ]);
    }

    /**
     * Toggle owner task completion status
     */
    public function toggleOwnerTask(Request $request, $id)
    {
        $task = Task::where('id', $id)
            ->where('staff_category', 'owner_personal')
            ->firstOrFail();

        $task->update(['is_done' => !$task->is_done]);

        return response()->json([
            'success' => true,
            'is_done' => (bool) $task->is_done
        ]);
    }

    /**
     * Delete owner's personal task
     */
    public function deleteOwnerTask(Request $request, $id)
    {
        $task = Task::where('id', $id)
            ->where('staff_category', 'owner_personal')
            ->firstOrFail();

        $task->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Get staff currently out on gate pass
     */
    public function getStaffOut(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $dateStart = Carbon::parse($date)->startOfDay();
        $dateEnd = Carbon::parse($date)->endOfDay();

        // Get all gate passes for the day
        $gatePasses = GatePass::with('person')
            ->whereBetween('exit_time', [$dateStart, $dateEnd])
            ->orderBy('exit_time', 'desc')
            ->get();

        // Currently out (active/approved, not returned)
        $currentlyOut = $gatePasses->filter(function ($pass) {
            return in_array($pass->status, ['active', 'approved']) && !$pass->actual_return;
        });

        // Returned today
        $returned = $gatePasses->filter(function ($pass) {
            return $pass->actual_return || $pass->status === 'returned';
        });

        // Overdue (expected return passed but not returned)
        $overdue = $currentlyOut->filter(function ($pass) {
            return $pass->is_overdue;
        });

        // Format the data
        $formattedPasses = $gatePasses->map(function ($pass) {
            $isOut = in_array($pass->status, ['active', 'approved']) && !$pass->actual_return;
            $isOverdue = $pass->is_overdue;
            
            return [
                'id' => $pass->id,
                'staff_name' => $pass->person->name ?? 'Unknown',
                'purpose' => $pass->formatted_purpose,
                'destination' => $pass->destination,
                'exit_time' => $pass->exit_time ? $pass->exit_time->format('H:i') : null,
                'expected_return' => $pass->expected_return ? $pass->expected_return->format('H:i') : null,
                'actual_return' => $pass->actual_return ? $pass->actual_return->format('H:i') : null,
                'duration' => $pass->formatted_duration,
                'status' => $pass->status,
                'is_out' => $isOut,
                'is_overdue' => $isOverdue,
                'contact' => $pass->contact_number,
            ];
        });

        return response()->json([
            'success' => true,
            'date' => $date,
            'stats' => [
                'total_today' => $gatePasses->count(),
                'currently_out' => $currentlyOut->count(),
                'returned' => $returned->count(),
                'overdue' => $overdue->count(),
            ],
            'passes' => $formattedPasses,
        ]);
    }

    /**
     * Dashboard Widget: Today's Arrivals & Departures
     */
    public function getArrivalsAndDepartures(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $dateStart = Carbon::parse($date)->startOfDay();
        $dateEnd = Carbon::parse($date)->endOfDay();

        // Arrivals: bookings whose start date is today
        $arrivals = Booking::whereDate('start', $date)
            ->orderBy('start', 'asc')
            ->get()
            ->map(function ($booking) {
                $rooms = is_array($booking->room_numbers) ? $booking->room_numbers : json_decode($booking->room_numbers, true) ?? [];
                return [
                    'id' => $booking->id,
                    'name' => $booking->name,
                    'function_type' => $booking->function_type,
                    'contact_number' => $booking->contact_number,
                    'check_in' => $booking->start ? $booking->start->format('h:i A') : null,
                    'check_out' => $booking->end ? $booking->end->format('h:i A') : null,
                    'rooms' => $rooms,
                    'room_count' => count($rooms),
                    'guest_count' => $booking->guest_count,
                ];
            });

        // Departures: bookings whose end date is today
        $departures = Booking::whereDate('end', $date)
            ->orderBy('end', 'asc')
            ->get()
            ->map(function ($booking) {
                $rooms = is_array($booking->room_numbers) ? $booking->room_numbers : json_decode($booking->room_numbers, true) ?? [];
                return [
                    'id' => $booking->id,
                    'name' => $booking->name,
                    'function_type' => $booking->function_type,
                    'contact_number' => $booking->contact_number,
                    'check_in' => $booking->start ? $booking->start->format('h:i A') : null,
                    'check_out' => $booking->end ? $booking->end->format('h:i A') : null,
                    'rooms' => $rooms,
                    'room_count' => count($rooms),
                    'guest_count' => $booking->guest_count,
                ];
            });

        // In-house: bookings that span across today (started before, ending after)
        $inHouse = Booking::where('start', '<', $dateStart)
            ->where('end', '>', $dateEnd)
            ->get()
            ->map(function ($booking) {
                $rooms = is_array($booking->room_numbers) ? $booking->room_numbers : json_decode($booking->room_numbers, true) ?? [];
                return [
                    'id' => $booking->id,
                    'name' => $booking->name,
                    'function_type' => $booking->function_type,
                    'rooms' => $rooms,
                    'room_count' => count($rooms),
                    'guest_count' => $booking->guest_count,
                ];
            });

        return response()->json([
            'success' => true,
            'date' => $date,
            'arrivals' => $arrivals,
            'departures' => $departures,
            'in_house' => $inHouse,
            'stats' => [
                'arrivals_count' => $arrivals->count(),
                'departures_count' => $departures->count(),
                'in_house_count' => $inHouse->count(),
            ],
        ]);
    }

    /**
     * Dashboard Widget: Housekeeping Status
     */
    public function getHousekeepingStatus(Request $request)
    {
        $rooms = Room::all();

        $roomStatuses = $rooms->map(function ($room) {
            $status = $room->housekeeping_status ?? 'available';
            return [
                'id' => $room->id,
                'name' => $room->name,
                'status' => $status,
            ];
        });

        $available = $roomStatuses->where('status', 'available')->count();
        $occupied = $roomStatuses->where('status', 'occupied')->count();
        $needsCleaning = $roomStatuses->where('status', 'needs_cleaning')->count();

        return response()->json([
            'success' => true,
            'rooms' => $roomStatuses,
            'stats' => [
                'total' => $rooms->count(),
                'available' => $available,
                'occupied' => $occupied,
                'needs_cleaning' => $needsCleaning,
            ],
        ]);
    }

    /**
     * Cycle room housekeeping status: available -> occupied -> needs_cleaning -> available
     */
    public function cycleRoomStatus(Request $request)
    {
        try {
            $room = Room::findOrFail($request->input('room_id'));
            $currentStatus = $room->housekeeping_status ?? 'available';

            $cycle = [
                'available' => 'occupied',
                'occupied' => 'needs_cleaning',
                'needs_cleaning' => 'available',
            ];

            $newStatus = $cycle[$currentStatus] ?? 'available';
            $room->housekeeping_status = $newStatus;
            $room->save();

            // Return updated stats
            $allRooms = Room::all();
            $available = $allRooms->where('housekeeping_status', 'available')->count() + $allRooms->whereNull('housekeeping_status')->count();
            $occupied = $allRooms->where('housekeeping_status', 'occupied')->count();
            $needsCleaning = $allRooms->where('housekeeping_status', 'needs_cleaning')->count();

            return response()->json([
                'success' => true,
                'room_id' => $room->id,
                'new_status' => $newStatus,
                'stats' => [
                    'total' => $allRooms->count(),
                    'available' => $available,
                    'occupied' => $occupied,
                    'needs_cleaning' => $needsCleaning,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Dashboard Widget: Inventory Warnings from /stock system
     */
    public function getInventoryWarnings(Request $request)
    {
        $today = Carbon::today()->toDateString();

        // Get the latest inventory record for each item (today or most recent before today)
        $items = Item::with(['group'])->get()
            ->map(function ($item) use ($today) {
                // Get today's inventory or the most recent one
                $inventory = Inventory::where('item_id', $item->id)
                    ->where('stock_date', '<=', $today)
                    ->orderBy('stock_date', 'desc')
                    ->first();

                $stockLevel = $inventory ? $inventory->stock_level : null;

                // Skip items with no inventory records at all
                if ($stockLevel === null) {
                    return null;
                }

                // Determine status based on stock level
                if ($stockLevel <= 0) {
                    $status = 'Out of Stock';
                } elseif ($stockLevel <= 5) {
                    $status = 'Low Stock';
                } else {
                    return null; // Not a warning item
                }

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'current_stock' => $stockLevel,
                    'category' => $item->group->name ?? 'Uncategorized',
                    'status' => $status,
                ];
            })
            ->filter()
            ->sortBy('current_stock')
            ->values();

        $outOfStock = $items->where('status', 'Out of Stock')->count();
        $lowStock = $items->where('status', 'Low Stock')->count();

        return response()->json([
            'success' => true,
            'items' => $items,
            'stats' => [
                'total_warnings' => $items->count(),
                'out_of_stock' => $outOfStock,
                'low_stock' => $lowStock,
            ],
        ]);
    }

    /**
     * Dashboard Widget: Pending CRM Leads
     */
    public function getPendingLeads(Request $request)
    {
        $stats = Lead::getStats();

        // Leads needing immediate action (need to contact + overdue follow-ups)
        $urgentLeads = Lead::pendingForCall()
            ->orderBy('next_follow_up_at', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($lead) {
                return [
                    'id' => $lead->id,
                    'customer_name' => $lead->customer_name,
                    'phone_number' => $lead->formatted_phone,
                    'whatsapp_link' => $lead->whatsapp_link,
                    'source' => $lead->source?->label() ?? 'Unknown',
                    'status' => $lead->status?->label() ?? 'Unknown',
                    'status_color' => $lead->status?->badgeColor() ?? 'secondary',
                    'check_in' => $lead->check_in ? $lead->check_in->format('M d') : null,
                    'check_out' => $lead->check_out ? $lead->check_out->format('M d') : null,
                    'is_overdue' => $lead->is_overdue,
                    'next_follow_up' => $lead->next_follow_up_at ? $lead->next_follow_up_at->format('M d, h:i A') : null,
                    'days_since_contact' => $lead->days_since_contact,
                ];
            });

        // Today's new leads
        $todayLeads = Lead::today()->count();

        return response()->json([
            'success' => true,
            'leads' => $urgentLeads,
            'stats' => $stats,
            'today_new' => $todayLeads,
        ]);
    }

    /**
     * Dashboard Widget: Maintenance / Damage Tickets
     */
    public function getMaintenanceTickets(Request $request)
    {
        // Get recent damage items (last 30 days, unresolved)
        $recentDamages = DamageItem::where('reported_date', '>=', Carbon::now()->subDays(30))
            ->orderBy('reported_date', 'desc')
            ->limit(15)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_name' => $item->item_name,
                    'quantity' => $item->quantity,
                    'type' => $item->type,
                    'notes' => $item->notes,
                    'total_cost' => $item->total_cost,
                    'reported_date' => $item->reported_date ? $item->reported_date->format('M d, Y') : null,
                    'days_ago' => $item->reported_date ? $item->reported_date->diffInDays(Carbon::today()) : null,
                ];
            });

        // Pending maintenance tasks
        $maintenanceTasks = Task::where('is_done', false)
            ->where(function ($q) {
                $q->where('staff_category', 'maintenance')
                  ->orWhere('person_incharge', 'like', '%maintenance%');
            })
            ->orderBy('priority_order', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'task' => $task->task,
                    'priority' => $task->priority_order,
                    'is_overdue' => $task->isOverdue(),
                    'due_date' => $task->end_date,
                    'assigned_to' => $task->assignedPerson ? $task->assignedPerson->name : ($task->person_incharge ?? 'Unassigned'),
                ];
            });

        return response()->json([
            'success' => true,
            'damages' => $recentDamages,
            'tasks' => $maintenanceTasks,
            'stats' => [
                'total_damages' => $recentDamages->count(),
                'pending_tasks' => $maintenanceTasks->count(),
            ],
        ]);
    }

    /**
     * Dashboard: Combined Command Center data (single call for initial load)
     */
    public function getCommandCenterData(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));

        // Arrivals count
        $arrivalsCount = Booking::whereDate('start', $date)->count();
        $departuresCount = Booking::whereDate('end', $date)->count();

        // Housekeeping summary
        $totalRooms = Room::count();
        $availableRooms = Room::where('housekeeping_status', 'available')->orWhereNull('housekeeping_status')->count();
        $occupiedRooms = Room::where('housekeeping_status', 'occupied')->count();
        $needsCleaningRooms = Room::where('housekeeping_status', 'needs_cleaning')->count();

        // Inventory warnings from /stock system
        $today = Carbon::today()->toDateString();
        $lowStockCount = 0;
        $outOfStockCount = 0;
        $allItems = Item::all();
        foreach ($allItems as $item) {
            $inv = Inventory::where('item_id', $item->id)
                ->where('stock_date', '<=', $today)
                ->orderBy('stock_date', 'desc')
                ->first();
            if ($inv) {
                if ($inv->stock_level <= 0) {
                    $outOfStockCount++;
                } elseif ($inv->stock_level <= 5) {
                    $lowStockCount++;
                }
            }
        }

        // CRM Leads
        $pendingLeads = Lead::pendingForCall()->count();
        $overdueLeads = Lead::overdue()->count();
        $todayNewLeads = Lead::today()->count();

        // Maintenance
        $pendingMaintenance = Task::where('is_done', false)
            ->where(function ($q) {
                $q->where('staff_category', 'maintenance')
                  ->orWhere('person_incharge', 'like', '%maintenance%');
            })
            ->count();

        $recentDamages = DamageItem::where('reported_date', '>=', Carbon::now()->subDays(7))->count();

        // In-house guests (bookings spanning today)
        $dateStart = Carbon::parse($date)->startOfDay();
        $dateEnd = Carbon::parse($date)->endOfDay();
        $inHouseCount = Booking::where('start', '<', $dateStart)
            ->where('end', '>', $dateEnd)
            ->count();

        // Tasks summary
        $tasksDueToday = Task::where('is_done', false)->whereDate('end_date', $date)->count();
        $tasksOverdue = Task::where('is_done', false)->whereNotNull('end_date')->whereDate('end_date', '<', $date)->count();

        // Pending customer feedback
        $feedbackPending = CustomerFeedback::pending()->count();

        return response()->json([
            'success' => true,
            'date' => $date,
            'summary' => [
                'arrivals' => $arrivalsCount,
                'departures' => $departuresCount,
                'in_house' => $inHouseCount,
                'rooms_available' => $availableRooms,
                'rooms_occupied' => $occupiedRooms,
                'rooms_dirty' => $needsCleaningRooms,
                'rooms_total' => $totalRooms,
                'inventory_low' => $lowStockCount,
                'inventory_out' => $outOfStockCount,
                'leads_pending' => $pendingLeads,
                'leads_overdue' => $overdueLeads,
                'leads_today' => $todayNewLeads,
                'maintenance_pending' => $pendingMaintenance,
                'damages_recent' => $recentDamages,
                'tasks_due_today' => $tasksDueToday,
                'tasks_overdue' => $tasksOverdue,
                'feedback_pending' => $feedbackPending,
            ],
        ]);
    }

    /**
     * Dashboard Widget: Pending Customer Feedback
     */
    public function getPendingFeedback(Request $request)
    {
        $feedbacks = CustomerFeedback::pending()
            ->orderBy('function_date', 'desc')
            ->limit(15)
            ->get()
            ->map(function ($fb) {
                return [
                    'id' => $fb->id,
                    'customer_name' => $fb->customer_name,
                    'contact_number' => $fb->contact_number,
                    'formatted_phone' => $fb->formatted_phone,
                    'whatsapp_link' => $fb->whatsapp_link,
                    'function_type' => $fb->function_type,
                    'function_date' => $fb->function_date ? $fb->function_date->format('M d') : null,
                    'days_ago' => $fb->function_date ? $fb->function_date->diffInDays(Carbon::today()) : null,
                ];
            });

        $totalPending = CustomerFeedback::pending()->count();
        $completedToday = CustomerFeedback::completed()
            ->whereDate('feedback_taken_at', Carbon::today())
            ->count();

        return response()->json([
            'success' => true,
            'feedbacks' => $feedbacks,
            'stats' => [
                'pending' => $totalPending,
                'completed_today' => $completedToday,
            ],
        ]);
    }

    /**
     * Dashboard Widget: Today's Tasks Summary (all categories)
     */
    public function getTodayTasks(Request $request)
    {
        try {
            $date = $request->input('date', date('Y-m-d'));

            // Tasks due today
            $dueToday = Task::where('is_done', false)
                ->whereDate('end_date', $date)
                ->with('assignedPerson')
                ->get()
                ->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'task' => $task->task,
                        'priority' => $task->priority_order ?? 'Medium',
                        'category' => $task->staff_category,
                        'assigned_to' => $task->assignedPerson ? $task->assignedPerson->name : ($task->person_incharge ?? 'Unassigned'),
                        'is_overdue' => false,
                    ];
                });

            // Overdue tasks
            $overdue = Task::where('is_done', false)
                ->whereNotNull('end_date')
                ->whereDate('end_date', '<', $date)
                ->with('assignedPerson')
                ->orderBy('end_date', 'asc')
                ->limit(10)
                ->get()
                ->map(function ($task) use ($date) {
                    return [
                        'id' => $task->id,
                        'task' => $task->task,
                        'priority' => $task->priority_order ?? 'Medium',
                        'category' => $task->staff_category,
                        'assigned_to' => $task->assignedPerson ? $task->assignedPerson->name : ($task->person_incharge ?? 'Unassigned'),
                        'is_overdue' => true,
                        'due_date' => $task->end_date,
                        'days_overdue' => Carbon::parse($task->end_date)->diffInDays(Carbon::parse($date)),
                    ];
                });

            // Completed today
            $completedToday = Task::where('is_done', true)
                ->whereDate('updated_at', $date)
                ->count();

            // Total pending
            $totalPending = Task::where('is_done', false)->count();

            return response()->json([
                'success' => true,
                'date' => $date,
                'due_today' => $dueToday,
                'overdue' => $overdue,
                'stats' => [
                    'due_today' => $dueToday->count(),
                    'overdue' => $overdue->count(),
                    'completed_today' => $completedToday,
                    'total_pending' => $totalPending,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'due_today' => [],
                'overdue' => [],
                'stats' => ['due_today' => 0, 'overdue' => 0, 'completed_today' => 0, 'total_pending' => 0],
            ]);
        }
    }

    /**
     * Dashboard Widget: Online Users & Activity (Admin Only)
     */
    public function getOnlineUsers(Request $request)
    {
        try {
            if (!auth()->user() || !auth()->user()->checkAdmin()) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            $users = User::select('id', 'name', 'role', 'email', 'last_seen_at', 'last_page', 'last_ip')
                ->orderByDesc('last_seen_at')
                ->get()
                ->map(function ($user) {
                    $lastSeen = $user->last_seen_at ? Carbon::parse($user->last_seen_at) : null;
                    $isOnline = $lastSeen && $lastSeen->diffInMinutes(Carbon::now()) < 5;
                    $isIdle = $lastSeen && !$isOnline && $lastSeen->diffInMinutes(Carbon::now()) < 15;

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'role' => $user->role,
                        'email' => $user->email,
                        'is_online' => $isOnline,
                        'is_idle' => $isIdle,
                        'last_seen_at' => $lastSeen ? $lastSeen->toDateTimeString() : null,
                        'last_seen_human' => $lastSeen ? $lastSeen->diffForHumans() : 'Never',
                        'last_page' => $user->last_page,
                        'last_ip' => $user->last_ip,
                    ];
                });

            $online = $users->where('is_online', true)->count();
            $idle = $users->where('is_idle', true)->count();
            $offline = $users->count() - $online - $idle;

            return response()->json([
                'success' => true,
                'users' => $users,
                'stats' => [
                    'total' => $users->count(),
                    'online' => $online,
                    'idle' => $idle,
                    'offline' => $offline,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
