<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InvProductCategory;
use App\Models\InvProduct;
use App\Models\InvInventory;
use App\Models\InvInventoryLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvInventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Display the inventory dashboard
    public function index(Request $request)
{
    $currentMonth = $request->input('month', now()->month);
    $currentYear = $request->input('year', now()->year);
    $categoryId = $request->input('category_id');
    $selectedDate = $request->input('log_date', now()->toDateString());

    $firstDayOfMonth = Carbon::createFromDate($currentYear, $currentMonth, 1);
    
    $categories = InvProductCategory::with(['products' => function ($query) {
        $query->with(['inventories' => function ($query) {
            $query->orderBy('stock_date', 'desc');
        }]);
    }]);

    if ($categoryId) {
        $categories = $categories->where('id', $categoryId);
    }

    $categories = $categories->get();

    // Get logs for the current month
    $monthLogs = InvInventoryLog::with(['user', 'product'])
        ->whereYear('created_at', $currentYear)
        ->whereMonth('created_at', $currentMonth)
        ->get();

    // Get paginated logs for selected date
    $logs = InvInventoryLog::with(['user', 'product'])
        ->whereDate('created_at', $selectedDate)
        ->orderBy('created_at', 'desc')
        ->paginate(5);

    // For each product, get the last known stock level before this month
    foreach ($categories as $category) {
        foreach ($category->products as $product) {
            $lastKnownStock = InvInventory::where('product_id', $product->id)
                ->where('stock_date', '<', $firstDayOfMonth->toDateString())
                ->orderBy('stock_date', 'desc')
                ->first();

            $product->lastKnownStockLevel = $lastKnownStock ? $lastKnownStock->stock_level : 0;
        }
    }

    return view('inventory.physical.index', compact(
        'categories',
        'currentMonth',
        'currentYear',
        'logs',
        'monthLogs'
    ));
}
    // Store a new category
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        InvProductCategory::create([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Category added successfully.');
    }
    // Store a new product
    public function storeProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:inv_product_categories,id',
        ]);

        InvProduct::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
        ]);

        return redirect()->back()->with('success', 'Product added successfully.');
    }

    // Update today's stock
    public function updateTodayStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:inv_products,id',
            'quantity' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'action' => 'required|in:add,remove',
        ]);

        $productId = $request->product_id;
        $quantity = $request->quantity;
        $description = $request->description;
        $action = $request->action;
        $today = now()->toDateString();

        DB::transaction(function () use ($productId, $quantity, $description, $action, $today) {
            // Get today's inventory record or create new one
            $inventory = InvInventory::firstOrNew(['product_id' => $productId, 'stock_date' => $today]);

            if (!$inventory->exists) {
                // If no record exists for today, get the last known stock level
                $previousInventory = InvInventory::where('product_id', $productId)
                    ->where('stock_date', '<', $today)
                    ->orderBy('stock_date', 'desc')
                    ->first();

                $inventory->stock_level = $previousInventory ? $previousInventory->stock_level : 0;
            }

            // Calculate new stock level based on action
            $originalStockLevel = $inventory->stock_level;

            if ($action === 'add') {
                $inventory->stock_level += $quantity;
            } else {
                $inventory->stock_level = max(0, $inventory->stock_level - $quantity);
            }

            $inventory->save();

            // Only propagate if stock level has changed
            if ($originalStockLevel !== $inventory->stock_level) {
                $this->propagateStock($productId, $today, $inventory->stock_level);
            }

            // Create log entry
            InvInventoryLog::create([
                'product_id' => $productId,
                'user_id' => Auth::id(),
                'action' => $action,
                'quantity' => $quantity,
                'description' => $description,
            ]);
        });

        return redirect()->back()->with('success', 'Stock updated successfully.');
    }

    // Propagate stock level to future dates
    private function propagateStock($productId, $startDate, $stockLevel)
{
    DB::transaction(function () use ($productId, $startDate, $stockLevel) {
        // Delete all future records
        InvInventory::where('product_id', $productId)
            ->where('stock_date', '>', $startDate)
            ->delete();

        // Create future records for the next 6 months
        $currentDate = Carbon::parse($startDate)->addDay();
        $endDate = Carbon::parse($startDate)->addMonths(6);

        while ($currentDate->lte($endDate)) {
            InvInventory::updateOrCreate(
                [
                    'product_id' => $productId,
                    'stock_date' => $currentDate->toDateString()
                ],
                [
                    'stock_level' => $stockLevel
                ]
            );
            $currentDate->addDay();
        }
    });
}

    // View monthly stock
    public function viewMonthlyStock(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $categories = InvProductCategory::with(['products.inventories' => function ($query) use ($month, $year) {
            $query->whereYear('stock_date', $year)
                  ->whereMonth('stock_date', $month)
                  ->orderBy('stock_date');
        }])->get();

        return view('inventory.physical.monthly', compact('categories', 'month', 'year'));
    }
    public function monthly(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $categories = InvCategory::with(['products.inventories' => function ($query) use ($month, $year) {
            $query->whereYear('stock_date', $year)
                  ->whereMonth('stock_date', $month);
        }])->get();

        return view('inventory.physical.monthly', compact('categories', 'month', 'year'));
    }
}
