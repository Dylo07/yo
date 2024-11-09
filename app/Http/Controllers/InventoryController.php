<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\ProductGroup;
use App\Models\Item;
use App\Models\Inventory;


class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $currentMonth = $request->input('month', now()->month);
        $currentYear = $request->input('year', now()->year);
    
        // Pad month with leading zero
        $formattedMonth = str_pad($currentMonth, 2, '0', STR_PAD_LEFT);
    
        $groups = ProductGroup::with(['items' => function ($query) use ($currentYear, $formattedMonth) {
            $query->with(['inventory' => function ($inventoryQuery) use ($currentYear, $formattedMonth) {
                $inventoryQuery->whereYear('stock_date', $currentYear)
                    ->whereMonth('stock_date', $formattedMonth);
            }]);
        }])->get();
    
        return view('stock.index', compact('groups', 'currentMonth', 'currentYear'));
    }
    public function store(Request $request)
{
    $request->validate([
        'item_id' => 'required|exists:items,id',
        'stock_date' => 'required|date',
        'stock_level' => 'required|integer',
    ]);

    $itemId = $request->item_id;
    $stockDate = $request->stock_date;
    $stockLevel = $request->stock_level;

    // Update or create stock for the given date
    \App\Models\Inventory::updateOrCreate(
        ['item_id' => $itemId, 'stock_date' => $stockDate],
        ['stock_level' => $stockLevel]
    );

    // Propagate stock level to future days
    DB::transaction(function () use ($itemId, $stockDate, $stockLevel) {
        $nextUpdates = \App\Models\Inventory::where('item_id', $itemId)
            ->where('stock_date', '>', $stockDate)
            ->orderBy('stock_date')
            ->get();

        if ($nextUpdates->isEmpty()) {
            // No future updates, apply the current stock level to all future dates
            for ($date = now()->parse($stockDate)->addDay(); $date->lte(now()); $date->addDay()) {
                \App\Models\Inventory::updateOrCreate(
                    ['item_id' => $itemId, 'stock_date' => $date->toDateString()],
                    ['stock_level' => $stockLevel]
                );
            }
        } else {
            // Propagate stock level until the next update
            $nextUpdateDate = $nextUpdates->first()->stock_date;
            for ($date = now()->parse($stockDate)->addDay(); $date->lt($nextUpdateDate); $date->addDay()) {
                \App\Models\Inventory::updateOrCreate(
                    ['item_id' => $itemId, 'stock_date' => $date->toDateString()],
                    ['stock_level' => $stockLevel]
                );
            }
        }
    });

    return redirect()->back()->with('success', 'Stock updated and propagated successfully.');
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
            $inventoryQuery->whereYear('stock_date', $year)->whereMonth('stock_date', $month);
        }]);
    }])->get();

    return view('stock.monthly', compact('groups', 'month', 'year'));
}

}
