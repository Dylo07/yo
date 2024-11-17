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

        $groups = ProductGroup::with(['items.inventory' => function ($query) use ($currentYear, $currentMonth) {
            $query->whereYear('stock_date', $currentYear)->whereMonth('stock_date', $currentMonth);
        }]);

        if ($categoryId) {
            $groups = $groups->where('id', $categoryId);
        }

        $groups = $groups->get();

        $logs = StockLog::with(['user', 'item'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends($request->except('page'));

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
        $endOfMonth = Carbon::parse($startDate)->endOfMonth();
        
        // Delete any existing future records to prevent conflicts
        Inventory::where('item_id', $itemId)
            ->where('stock_date', '>', $startDate)
            ->where('stock_date', '<=', $endOfMonth)
            ->delete();
            
        // Propagate the current stock level to all future dates until end of month
        $currentDate = $startDateCarbon->copy()->addDay();
        
        while ($currentDate->lte($endOfMonth)) {
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
        // If no dates provided, set default range (today to next 7 days)
        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->addDays(7)->toDateString());

        // Get all groups and items for the dropdown
        $groups = ProductGroup::with('items')->get();

        // If an item is selected, get its stock data
        if ($request->has('item_id')) {
            $request->validate([
                'item_id' => 'required|exists:items,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $item = Item::findOrFail($request->item_id);
            
            $stockLevels = Inventory::where('item_id', $request->item_id)
                ->whereBetween('stock_date', [$request->start_date, $request->end_date])
                ->orderBy('stock_date')
                ->get()
                ->map(function ($inventory) {
                    return [
                        'date' => $inventory->stock_date,
                        'stock_level' => $inventory->stock_level,
                    ];
                });

            if ($request->wantsJson()) {
                return response()->json($stockLevels);
            }

            return view('stock.propagation-test', compact('stockLevels', 'item', 'groups', 'startDate', 'endDate'));
        }

        // Initial page load without item selection
        return view('stock.propagation-test', compact('groups', 'startDate', 'endDate'));
    }
   
}
