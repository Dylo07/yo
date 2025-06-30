<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Menu;

class SimpleRecipeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show recipe management page
     */
    public function index()
    {
        // Get all menus with their recipes
        $menus = Menu::with('category')->orderBy('name')->get();
        
        // Get kitchen items for the dropdown
        $kitchenItems = DB::table('items')
            ->where('is_kitchen_item', true)
            ->where('kitchen_is_active', true)
            ->orderBy('name')
            ->get();

        // Get existing recipes
        $recipes = DB::table('menu_item_recipes')
            ->join('menus', 'menu_item_recipes.menu_id', '=', 'menus.id')
            ->join('items', 'menu_item_recipes.item_id', '=', 'items.id')
            ->select(
                'menu_item_recipes.*',
                'menus.name as menu_name',
                'items.name as item_name',
                'items.kitchen_unit',
                'items.kitchen_current_stock'
            )
            ->orderBy('menus.name')
            ->get()
            ->groupBy('menu_id');

        return view('recipes.simple-manage', compact('menus', 'kitchenItems', 'recipes'));
    }

    /**
     * Save recipe for a menu item
     */
    public function saveRecipe(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.item_id' => 'required|exists:items,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.001'
        ]);

        try {
            DB::beginTransaction();

            // Delete existing recipes for this menu
            DB::table('menu_item_recipes')->where('menu_id', $request->menu_id)->delete();

            // Add new recipes
            foreach ($request->ingredients as $ingredient) {
                DB::table('menu_item_recipes')->insert([
                    'menu_id' => $request->menu_id,
                    'item_id' => $ingredient['item_id'],
                    'required_quantity' => $ingredient['quantity'],
                    'preparation_notes' => $ingredient['notes'] ?? null,
                    'is_optional' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Recipe saved successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error saving recipe: ' . $e->getMessage());
        }
    }

    /**
     * Get recipe for a specific menu (AJAX)
     */
    public function getRecipe($menuId)
    {
        $recipe = DB::table('menu_item_recipes')
            ->join('items', 'menu_item_recipes.item_id', '=', 'items.id')
            ->where('menu_item_recipes.menu_id', $menuId)
            ->select(
                'menu_item_recipes.*',
                'items.name as item_name',
                'items.kitchen_unit',
                'items.kitchen_current_stock'
            )
            ->get();

        return response()->json($recipe);
    }

    /**
     * Process kitchen consumption when a sale is made (call this from CashierController)
     */
    public function processKitchenConsumption($saleId)
    {
        try {
            // Get sale details
            $saleDetails = DB::table('sale_details')
                ->where('sale_id', $saleId)
                ->get();

            foreach ($saleDetails as $saleDetail) {
                // Get recipe for this menu item
                $recipes = DB::table('menu_item_recipes')
                    ->join('items', 'menu_item_recipes.item_id', '=', 'items.id')
                    ->where('menu_item_recipes.menu_id', $saleDetail->menu_id)
                    ->where('items.is_kitchen_item', true)
                    ->select(
                        'menu_item_recipes.*',
                        'items.name as item_name',
                        'items.kitchen_current_stock'
                    )
                    ->get();

                foreach ($recipes as $recipe) {
                    $totalRequired = $recipe->required_quantity * $saleDetail->quantity;
                    
                    // Check if we have enough stock
                    if ($recipe->kitchen_current_stock >= $totalRequired) {
                        // Deduct from kitchen stock
                        $oldStock = $recipe->kitchen_current_stock;
                        $newStock = $oldStock - $totalRequired;

                        // Update kitchen stock
                        DB::table('items')
                            ->where('id', $recipe->item_id)
                            ->update([
                                'kitchen_current_stock' => $newStock,
                                'updated_at' => now()
                            ]);

                        // Log the consumption
                        DB::table('kitchen_stock_logs')->insert([
                            'item_id' => $recipe->item_id,
                            'action' => 'menu_consumption',
                            'quantity_before' => $oldStock,
                            'quantity_change' => -$totalRequired,
                            'quantity_after' => $newStock,
                            'description' => "Auto consumption: {$saleDetail->menu_name} x{$saleDetail->quantity} (Sale #{$saleId})",
                            'user_id' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    } else {
                        // Log insufficient stock warning
                        \Log::warning("Insufficient kitchen stock", [
                            'sale_id' => $saleId,
                            'menu_item' => $saleDetail->menu_name,
                            'ingredient' => $recipe->item_name,
                            'required' => $totalRequired,
                            'available' => $recipe->kitchen_current_stock
                        ]);
                    }
                }
            }

        } catch (\Exception $e) {
            \Log::error("Error processing kitchen consumption: " . $e->getMessage());
        }
    }

    /**
     * Get daily consumption report
     */
   public function getDailyConsumption(Request $request)
    {
        try {
            $date = $request->input('date', now()->toDateString());
            $startDate = \Carbon\Carbon::parse($date)->startOfDay();
            $endDate = \Carbon\Carbon::parse($date)->endOfDay();

            \Log::info('Getting daily consumption for date: ' . $date);

            // Check if kitchen_stock_logs table exists
            if (!DB::getSchemaBuilder()->hasTable('kitchen_stock_logs')) {
                \Log::warning('kitchen_stock_logs table does not exist');
                return response()->json([
                    'date' => $date,
                    'consumption' => [],
                    'total_cost' => 0,
                    'error' => 'Kitchen stock logs table not found'
                ]);
            }

            // Get kitchen consumption for the day with better error handling
            $consumption = DB::table('kitchen_stock_logs')
                ->join('items', 'kitchen_stock_logs.item_id', '=', 'items.id')
                ->leftJoin('product_groups', 'items.group_id', '=', 'product_groups.id')
                ->where('kitchen_stock_logs.action', 'menu_consumption')
                ->whereBetween('kitchen_stock_logs.created_at', [$startDate, $endDate])
                ->select(
                    'items.name as item_name',
                    'product_groups.name as category_name',
                    'items.kitchen_unit',
                    'items.kitchen_cost_per_unit',
                    DB::raw('SUM(ABS(kitchen_stock_logs.quantity_change)) as total_consumed'),
                    DB::raw('SUM(ABS(kitchen_stock_logs.quantity_change) * COALESCE(items.kitchen_cost_per_unit, 0)) as total_cost')
                )
                ->groupBy('items.id', 'items.name', 'product_groups.name', 'items.kitchen_unit', 'items.kitchen_cost_per_unit')
                ->orderBy('total_cost', 'desc')
                ->get();

            \Log::info('Consumption query result count: ' . $consumption->count());

            // If no consumption found, try to get some test data or show helpful info
            if ($consumption->isEmpty()) {
                // Check if there are any kitchen_stock_logs at all
                $totalLogs = DB::table('kitchen_stock_logs')->count();
                $consumptionLogs = DB::table('kitchen_stock_logs')
                    ->where('action', 'menu_consumption')
                    ->count();

                \Log::info("Total kitchen logs: $totalLogs, Consumption logs: $consumptionLogs");

                return response()->json([
                    'date' => $date,
                    'consumption' => [],
                    'total_cost' => 0,
                    'debug_info' => [
                        'total_kitchen_logs' => $totalLogs,
                        'consumption_logs' => $consumptionLogs,
                        'date_range' => [$startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')]
                    ]
                ]);
            }

            $totalCost = $consumption->sum('total_cost');

            return response()->json([
                'date' => $date,
                'consumption' => $consumption,
                'total_cost' => $totalCost
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in getDailyConsumption: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'date' => $request->input('date', 'not provided')
            ]);

            return response()->json([
                'date' => $request->input('date', now()->toDateString()),
                'consumption' => [],
                'total_cost' => 0,
                'error' => 'Database error: ' . $e->getMessage(),
                'debug' => config('app.debug') ? $e->getTraceAsString() : 'Enable debug mode for more details'
            ], 500);
        }
    }


    /**
     * Check if menu can be prepared with current stock
     */
    public function checkMenuAvailability($menuId)
    {
        $recipes = DB::table('menu_item_recipes')
            ->join('items', 'menu_item_recipes.item_id', '=', 'items.id')
            ->where('menu_item_recipes.menu_id', $menuId)
            ->where('items.is_kitchen_item', true)
            ->select(
                'menu_item_recipes.required_quantity',
                'items.name as item_name',
                'items.kitchen_current_stock',
                'items.kitchen_unit'
            )
            ->get();

        $canPrepare = true;
        $missingItems = [];

        foreach ($recipes as $recipe) {
            if ($recipe->kitchen_current_stock < $recipe->required_quantity) {
                $canPrepare = false;
                $missingItems[] = [
                    'item' => $recipe->item_name,
                    'required' => $recipe->required_quaSimpleRecipeControllerntity,
                    'available' => $recipe->kitchen_current_stock,
                    'unit' => $recipe->kitchen_unit
                ];
            }
        }

        return response()->json([
            'can_prepare' => $canPrepare,
            'missing_items' => $missingItems
        ]);
    }
}