<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UltraSimpleKitchenController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display kitchen inventory
     */
    public function index()
    {
        // Get kitchen items using raw DB queries
        $kitchenItems = DB::table('items')
            ->leftJoin('product_groups', 'items.group_id', '=', 'product_groups.id')
            ->select(
                'items.*',
                'product_groups.name as group_name'
            )
            ->where('items.is_kitchen_item', true)
            ->orderBy('items.name')
            ->paginate(20);

        // Get stats
        $stats = [
            'total_items' => DB::table('items')->where('is_kitchen_item', true)->count(),
            'low_stock_count' => DB::table('items')
                ->where('is_kitchen_item', true)
                ->whereRaw('kitchen_current_stock <= kitchen_minimum_stock')
                ->count(),
            'out_of_stock_count' => DB::table('items')
                ->where('is_kitchen_item', true)
                ->where('kitchen_current_stock', '<=', 0)
                ->count(),
            'total_value' => DB::table('items')
                ->where('is_kitchen_item', true)
                ->sum(DB::raw('kitchen_current_stock * kitchen_cost_per_unit')) ?? 0
        ];

        // Get available items (not kitchen items)
        $availableItems = DB::table('items')
            ->leftJoin('product_groups', 'items.group_id', '=', 'product_groups.id')
            ->select(
                'items.id',
                'items.name',
                'product_groups.name as group_name'
            )
            ->where('items.is_kitchen_item', false)
            ->orderBy('items.name')
            ->get();

        return view('kitchen.ultra-simple', compact('kitchenItems', 'stats', 'availableItems'));
    }

    /**
     * Add item to kitchen
     */
    public function addToKitchen(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'kitchen_unit' => 'required|string|max:50',
            'kitchen_current_stock' => 'required|numeric|min:0',
            'kitchen_minimum_stock' => 'required|numeric|min:0',
            'kitchen_cost_per_unit' => 'required|numeric|min:0',
            'kitchen_description' => 'nullable|string'
        ]);

        try {
            // Check if item is already a kitchen item
            $existingKitchenItem = DB::table('items')
                ->where('id', $request->item_id)
                ->where('is_kitchen_item', true)
                ->first();

            if ($existingKitchenItem) {
                return redirect()->back()->with('error', 'This item is already in kitchen inventory');
            }

            // Update the item to be a kitchen item
            DB::table('items')
                ->where('id', $request->item_id)
                ->update([
                    'is_kitchen_item' => true,
                    'kitchen_unit' => $request->kitchen_unit,
                    'kitchen_current_stock' => $request->kitchen_current_stock,
                    'kitchen_minimum_stock' => $request->kitchen_minimum_stock,
                    'kitchen_cost_per_unit' => $request->kitchen_cost_per_unit,
                    'kitchen_description' => $request->kitchen_description,
                    'kitchen_is_active' => true,
                    'updated_at' => now()
                ]);

            // Log the initial stock
            if ($request->kitchen_current_stock > 0) {
                DB::table('kitchen_stock_logs')->insert([
                    'item_id' => $request->item_id,
                    'action' => 'set',
                    'quantity_before' => 0,
                    'quantity_change' => $request->kitchen_current_stock,
                    'quantity_after' => $request->kitchen_current_stock,
                    'description' => 'Initial kitchen stock setup',
                    'user_id' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            return redirect()->back()->with('success', 'Item added to kitchen inventory successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error adding item: ' . $e->getMessage());
        }
    }

    /**
     * Update kitchen stock
     */
    public function updateStock(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'action' => 'required|in:add,remove,set',
            'quantity' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500'
        ]);

        try {
            // Get current item data
            $item = DB::table('items')
                ->where('id', $request->item_id)
                ->where('is_kitchen_item', true)
                ->first();

            if (!$item) {
                return redirect()->back()->with('error', 'Kitchen item not found');
            }

            $oldStock = $item->kitchen_current_stock;
            $quantityChange = $request->quantity;

            // Calculate new stock based on action
            switch ($request->action) {
                case 'add':
                    $newStock = $oldStock + $quantityChange;
                    break;
                case 'remove':
                    $newStock = $oldStock - $quantityChange;
                    $quantityChange = -$quantityChange; // Make it negative for logging
                    break;
                case 'set':
                    $newStock = $request->quantity;
                    $quantityChange = $newStock - $oldStock;
                    break;
            }

            // Prevent negative stock
            if ($newStock < 0) {
                return redirect()->back()->with('error', 'Cannot have negative stock. Current stock: ' . $oldStock);
            }

            // Update the stock
            DB::table('items')
                ->where('id', $request->item_id)
                ->update([
                    'kitchen_current_stock' => $newStock,
                    'updated_at' => now()
                ]);

            // Log the stock movement
            DB::table('kitchen_stock_logs')->insert([
                'item_id' => $request->item_id,
                'action' => $request->action,
                'quantity_before' => $oldStock,
                'quantity_change' => $quantityChange,
                'quantity_after' => $newStock,
                'description' => $request->description,
                'user_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->back()->with('success', 'Stock updated successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating stock: ' . $e->getMessage());
        }
    }

    /**
     * Update kitchen item details
     */
    public function updateItem(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'kitchen_unit' => 'required|string|max:50',
            'kitchen_minimum_stock' => 'required|numeric|min:0',
            'kitchen_cost_per_unit' => 'required|numeric|min:0',
            'kitchen_description' => 'nullable|string'
        ]);

        try {
            DB::table('items')
                ->where('id', $request->item_id)
                ->where('is_kitchen_item', true)
                ->update([
                    'kitchen_unit' => $request->kitchen_unit,
                    'kitchen_minimum_stock' => $request->kitchen_minimum_stock,
                    'kitchen_cost_per_unit' => $request->kitchen_cost_per_unit,
                    'kitchen_description' => $request->kitchen_description,
                    'updated_at' => now()
                ]);

            return redirect()->back()->with('success', 'Kitchen item updated successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating item: ' . $e->getMessage());
        }
    }

    /**
     * Remove item from kitchen
     */
    public function removeFromKitchen(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id'
        ]);

        try {
            DB::table('items')
                ->where('id', $request->item_id)
                ->update([
                    'is_kitchen_item' => false,
                    'kitchen_is_active' => false,
                    'updated_at' => now()
                ]);

            return redirect()->back()->with('success', 'Item removed from kitchen inventory');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error removing item: ' . $e->getMessage());
        }
    }

    /**
     * Get stock logs for an item
     */
    public function getStockLogs($itemId)
    {
        $logs = DB::table('kitchen_stock_logs')
            ->leftJoin('users', 'kitchen_stock_logs.user_id', '=', 'users.id')
            ->leftJoin('items', 'kitchen_stock_logs.item_id', '=', 'items.id')
            ->select(
                'kitchen_stock_logs.*',
                'users.name as user_name',
                'items.name as item_name'
            )
            ->where('kitchen_stock_logs.item_id', $itemId)
            ->orderBy('kitchen_stock_logs.created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json($logs);
    }
}