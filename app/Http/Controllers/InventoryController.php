<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\ProductGroup;
use App\Models\Item;
use App\Models\Inventory;
use App\Models\StockLog;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $currentMonth = $request->input('month', now()->month);
        $currentYear = $request->input('year', now()->year);
        $categoryId = $request->input('category_id');
        $selectedDate = $request->input('log_date', now()->toDateString());
        
        // Create Carbon instances for the first and last day of the month
        $firstDayOfMonth = Carbon::createFromDate($currentYear, $currentMonth, 1);
        $lastDayOfPreviousMonth = $firstDayOfMonth->copy()->subDay();
        
        // Get all groups for the dropdown
        $groups = ProductGroup::all();
        
        // If category is selected, load its items with inventory
        if ($categoryId) {
            $selectedGroup = ProductGroup::with(['items' => function ($query) use ($currentYear, $currentMonth, $lastDayOfPreviousMonth) {
                $query->with(['inventory' => function ($invQuery) use ($currentYear, $currentMonth, $lastDayOfPreviousMonth) {
                    $invQuery->where(function ($q) use ($currentYear, $currentMonth, $lastDayOfPreviousMonth) {
                        $q->whereYear('stock_date', $currentYear)
                          ->whereMonth('stock_date', $currentMonth)
                          ->orWhere('stock_date', $lastDayOfPreviousMonth->toDateString());
                    })->orderBy('stock_date');
                }]);
            }])->find($categoryId);
            
            if ($selectedGroup) {
                $groups = $groups->map(function ($group) use ($selectedGroup) {
                    if ($group->id === $selectedGroup->id) {
                        return $selectedGroup;
                    }
                    return $group;
                });
            }
        }
        
        // Get logs only for the selected date
        $logs = StockLog::with(['user', 'item'])
            ->whereDate('created_at', $selectedDate)
            ->orderBy('created_at', 'desc')
            ->get();
            
    
        return view('stock.index', compact('groups', 'currentMonth', 'currentYear', 'logs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'stock_date' => 'required|date',
            'stock_level' => 'required|numeric|min:0.1',
        ]);

        $itemId = $request->item_id;
        $stockDate = $request->stock_date;
        $stockLevel = $request->stock_level;

        // Update or create stock for the given date
        Inventory::updateOrCreate(
            ['item_id' => $itemId, 'stock_date' => $stockDate],
            ['stock_level' => $stockLevel]
        );

        // Propagate stock level to future days
        $this->propagateStock($itemId, $stockDate, $stockLevel);

        return redirect()->back()->with('success', 'Stock updated and propagated successfully.');
    }

    private function propagateStock($itemId, $startDate, $stockLevel)
    {
        // Convert to Carbon instance for date manipulation
        $startDateCarbon = Carbon::parse($startDate);
        
        // Get the end of current month as the maximum date for propagation
        $endOfNextMonth = Carbon::parse($startDate)->addMonths(3)->endOfMonth();
        
        // Delete any existing future records to prevent conflicts
        Inventory::where('item_id', $itemId)
            ->where('stock_date', '>', $startDate)
            ->where('stock_date', '<=', $endOfNextMonth)
            ->delete();
            
        // Propagate the current stock level to all future dates until end of month
        $currentDate = $startDateCarbon->copy()->addDay();
        
        while ($currentDate->lte($endOfNextMonth)) {
            Inventory::create([
                'item_id' => $itemId,
                'stock_date' => $currentDate->toDateString(),
                'stock_level' => $stockLevel
            ]);
            
            $currentDate->addDay();
        }
    }


    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        ProductGroup::create([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Category added successfully.');
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'group_id' => 'required|exists:product_groups,id',
        ]);

        Item::create([
            'name' => $request->name,
            'group_id' => $request->group_id,
        ]);

        return redirect()->back()->with('success', 'Item added successfully.');
    }

    public function viewMonthlyStock(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:' . now()->year,
        ]);

        $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);
        $year = $request->year;

        $groups = ProductGroup::with(['items' => function ($query) use ($month, $year) {
            $query->with(['inventory' => function ($inventoryQuery) use ($month, $year) {
                $inventoryQuery->whereYear('stock_date', $year)
                    ->whereMonth('stock_date', $month)
                    ->orderBy('stock_date');
            }]);
        }])->get();

        return view('stock.monthly', compact('groups', 'month', 'year'));
    }
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function updateTodayStock(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.1',
            'description' => 'required|string|max:255',
        ]);

        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to update stock.');
        }

        $itemId = $request->item_id;
        $quantity = $request->quantity;
        $description = $request->description;
        $today = now()->toDateString();

        DB::transaction(function () use ($itemId, $quantity, $description, $today, $request) {
            // Get today's inventory record or create new one
            $inventory = Inventory::firstOrNew(['item_id' => $itemId, 'stock_date' => $today]);

            if (!$inventory->exists) {
                $previousInventory = Inventory::where('item_id', $itemId)
                    ->where('stock_date', '<', $today)
                    ->orderBy('stock_date', 'desc')
                    ->first();

                $inventory->stock_level = $previousInventory ? $previousInventory->stock_level : 0;
            }

            // Calculate new stock level based on action
            $action = $request->action === 'add' ? 'add' : 'remove';
            $originalStockLevel = $inventory->stock_level;

            if ($action === 'add') {
                $inventory->stock_level += $quantity;
            } else {
                $inventory->stock_level = max(0, $inventory->stock_level - $quantity);
            }

            $inventory->save();

            // Only propagate if the stock level has changed
            if ($originalStockLevel !== $inventory->stock_level) {
                $this->propagateStock($itemId, $today, $inventory->stock_level);
            }

            // Create stock log with explicit user_id
            StockLog::create([
                'item_id' => $itemId,
                'user_id' => Auth::id(), // Explicitly get the user ID
                'action' => $action,
                'quantity' => $quantity,
                'description' => $description,
            ]);
        });

        return redirect()->back()->with('success', 'Stock updated successfully.');
    }


    public function checkStockPropagation(Request $request)
{
    $startDate = $request->input('start_date', now()->toDateString());
    $endDate = $request->input('end_date', now()->addDays(7)->toDateString());
    $groups = ProductGroup::with('items')->get();

    if ($request->has('item_id')) {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $item = Item::findOrFail($request->item_id);
        
        // Get the last known stock level before the start date
        $lastKnownStock = Inventory::where('item_id', $request->item_id)
            ->where('stock_date', '<=', $request->start_date)
            ->orderBy('stock_date', 'desc')
            ->first();

        $stockLevel = $lastKnownStock ? $lastKnownStock->stock_level : 0;
        
        // Generate date range with this stock level
        $stockLevels = collect();
        $currentDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        
        while ($currentDate <= $endDate) {
            $dateRecord = Inventory::where('item_id', $request->item_id)
                ->where('stock_date', $currentDate->toDateString())
                ->first();
                
            $stockLevels->push([
                'date' => $currentDate->toDateString(),
                'stock_level' => $dateRecord ? $dateRecord->stock_level : $stockLevel
            ]);
            
            $currentDate->addDay();
        }

        return view('stock.propagation-test', compact('stockLevels', 'item', 'groups', 'startDate', 'endDate'));
    }

    return view('stock.propagation-test', compact('groups', 'startDate', 'endDate'));
}
   
}
