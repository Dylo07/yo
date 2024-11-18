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

        $categories = InvProductCategory::with(['products.inventories' => function ($query) use ($currentYear, $currentMonth) {
            $query->whereYear('stock_date', $currentYear)
                  ->whereMonth('stock_date', $currentMonth);
        }]);

        if ($categoryId) {
            $categories = $categories->where('id', $categoryId);
        }

        $categories = $categories->get();

        $logs = InvInventoryLog::with(['user', 'product'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends($request->except('page'));

        return view('inv_inventory.index', compact('categories', 'currentMonth', 'currentYear', 'logs'));
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
            'quantity' => 'required|numeric|min:0.1',
            'description' => 'required|string|max:255',
            'action' => 'required|in:add,remove',
        ]);

        $productId = $request->product_id;
        $quantity = $request->quantity;
        $description = $request->description;
        $action = $request->action;
        $today = now()->toDateString();

        DB::transaction(function () use ($productId, $quantity, $description, $action, $today) {
            $inventory = InvInventory::firstOrNew(['product_id' => $productId, 'stock_date' => $today]);

            if (!$inventory->exists) {
                $previousInventory = InvInventory::where('product_id', $productId)
                    ->where('stock_date', '<', $today)
                    ->orderBy('stock_date', 'desc')
                    ->first();

                $inventory->stock_level = $previousInventory ? $previousInventory->stock_level : 0;
            }

            $originalStockLevel = $inventory->stock_level;

            if ($action === 'add') {
                $inventory->stock_level += $quantity;
            } else {
                $inventory->stock_level = max(0, $inventory->stock_level - $quantity);
            }

            $inventory->save();

            if ($originalStockLevel !== $inventory->stock_level) {
                $this->propagateStock($productId, $today, $inventory->stock_level);
            }

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
        $startDateCarbon = Carbon::parse($startDate);
        $endOfMonth = $startDateCarbon->copy()->endOfMonth();

        InvInventory::where('product_id', $productId)
            ->where('stock_date', '>', $startDate)
            ->where('stock_date', '<=', $endOfMonth)
            ->delete();

        $currentDate = $startDateCarbon->copy()->addDay();

        while ($currentDate->lte($endOfMonth)) {
            InvInventory::create([
                'product_id' => $productId,
                'stock_date' => $currentDate->toDateString(),
                'stock_level' => $stockLevel,
            ]);

            $currentDate->addDay();
        }
    }

    // View monthly stock
    public function viewMonthlyStock(Request $request)
    {
        $maxYear = now()->year + 5;
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:' . $maxYear,
        ]);

        $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);
        $year = $request->year;

        $categories = InvProductCategory::with(['products' => function ($query) use ($month, $year) {
            $query->with(['inventories' => function ($inventoryQuery) use ($month, $year) {
                $inventoryQuery->whereYear('stock_date', $year)
                    ->whereMonth('stock_date', $month)
                    ->orderBy('stock_date');
            }]);
        }])->get();

        return view('inv_inventory.monthly', compact('categories', 'month', 'year'));
    }
}
