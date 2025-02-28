<?php

namespace App\Http\Controllers\inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InStock;
use App\Models\Menu;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        // Define the specific category IDs we want to show
        $categoryIds = [4, 5, 28, 29];
        
        // Get categories and their menus
        $categories = Category::whereIn('id', $categoryIds)->get();
        $menus = Menu::whereIn('category_id', $categoryIds)
            ->with('category') // Eager load the category relationship
            ->orderBy('name') // Order items alphabetically by name
            ->get()
            ->groupBy('category_id'); // Group items by category
        
        // Get daily sales for today by default
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $dailySales = $this->getDailySales($date);
        
        $data = array(
            'menus' => $menus, 
            'categories' => $categories,
            'dailySales' => $dailySales,
            'selectedDate' => $date
        );
        
        return view('inventory.stock')->with('data', $data);
    }

    /**
     * Get daily sales for a specific date
     * 
     * @param string $date Date in Y-m-d format
     * @return array
     */
    private function getDailySales($date)
    {
        try {
            // Get negative stock entries (sales) for the specified date
            $sales = InStock::where('stock', '<', 0)
                ->whereDate('created_at', $date)
                ->with(['menu', 'menu.category', 'user'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Group sales by category
            $salesByCategory = [];
            $totalItems = 0;
            
            foreach ($sales as $sale) {
                // Skip items without menu relation
                if (!$sale->menu) {
                    \Log::warning("InStock ID: {$sale->id} has invalid menu_id: {$sale->menu_id}");
                    continue;
                }
                
                // Safely get category data
                $categoryId = $sale->menu->category_id ?? 0;
                $categoryName = 'Uncategorized';
                
                // Only try to access category properties if the relation exists
                if ($sale->menu->category) {
                    $categoryName = $sale->menu->category->name;
                }
                
                // Initialize category group if not exists
                if (!isset($salesByCategory[$categoryId])) {
                    $salesByCategory[$categoryId] = [
                        'name' => $categoryName,
                        'items' => [],
                        'total' => 0
                    ];
                }
                
                // Safely get user data
                $userName = ($sale->user) ? $sale->user->name : 'Unknown';
                
                // Add to items list if not already present, otherwise update count
                $itemFound = false;
                foreach ($salesByCategory[$categoryId]['items'] as &$item) {
                    if (isset($item['menu_id']) && $item['menu_id'] == $sale->menu_id) {
                        $item['quantity'] += abs($sale->stock);
                        $itemFound = true;
                        break;
                    }
                }
                
                if (!$itemFound) {
                    $salesByCategory[$categoryId]['items'][] = [
                        'menu_id' => $sale->menu_id,
                        'name' => $sale->menu->name ?? 'Unknown Item',
                        'quantity' => abs($sale->stock),
                        'user' => $userName
                    ];
                }
                
                $salesByCategory[$categoryId]['total'] += abs($sale->stock);
                $totalItems += abs($sale->stock);
            }
            
            // Sort categories by total sales (descending)
            uasort($salesByCategory, function($a, $b) {
                return $b['total'] - $a['total'];
            });
            
            return [
                'by_category' => $salesByCategory,
                'total_items' => $totalItems,
                'date' => $date
            ];
        } catch (\Exception $e) {
            \Log::error("Error in getDailySales: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            // Return empty but valid data structure
            return [
                'by_category' => [],
                'total_items' => 0,
                'date' => $date,
                'error' => $e->getMessage()
            ];
        }
    }
/**
 * Get daily sales data via AJAX - simplified version
 * 
 * @param Request $request
 * @return \Illuminate\Http\Response
 */
public function getDailySalesData(Request $request)
{
    try {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        
        // Verify InStock table name
        $inStockModel = new InStock();
        $tableName = $inStockModel->getTable();
        
        // Very basic query to avoid complex relationships
        $sales = DB::table($tableName)
            ->where('stock', '<', 0)
            ->whereDate('created_at', $date)
            ->get();
        
        // Simplify the response format
        $totalItems = 0;
        $items = [];
        
        foreach ($sales as $sale) {
            $totalItems += abs($sale->stock);
            
            // Get menu info directly to avoid relationship issues
            $menu = DB::table('menus')->find($sale->menu_id);
            
            if ($menu) {
                $menuName = $menu->name;
                $categoryId = $menu->category_id;
                
                // Get category name
                $category = DB::table('categories')->find($categoryId);
                $categoryName = $category ? $category->name : 'Uncategorized';
                
                if (!isset($items[$categoryId])) {
                    $items[$categoryId] = [
                        'name' => $categoryName,
                        'items' => [],
                        'total' => 0
                    ];
                }
                
                // Try to find matching item
                $found = false;
                foreach ($items[$categoryId]['items'] as &$item) {
                    if ($item['menu_id'] == $sale->menu_id) {
                        $item['quantity'] += abs($sale->stock);
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $items[$categoryId]['items'][] = [
                        'menu_id' => $sale->menu_id,
                        'name' => $menuName,
                        'quantity' => abs($sale->stock),
                        'user' => 'Staff'  // Simplified
                    ];
                }
                
                $items[$categoryId]['total'] += abs($sale->stock);
            }
        }
        
        return response()->json([
            'by_category' => $items,
            'total_items' => $totalItems,
            'date' => $date
        ]);
    } catch (\Exception $e) {
        // Log error but return a simplified response
        \Log::error("Error in simplified getDailySalesData: " . $e->getMessage());
        
        return response()->json([
            'message' => 'An error occurred: ' . $e->getMessage(),
            'by_category' => [],
            'total_items' => 0
        ], 500);
    }
}
    /**
     * Check if date is valid
     * 
     * @param string $date
     * @return bool
     */
    private function isValidDate($date)
    {
        if (!$date) return false;
        
        try {
            $d = Carbon::createFromFormat('Y-m-d', $date);
            return $d && $d->format('Y-m-d') === $date;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $stock = new InStock();
        $stock->menu_id  = $request->itemid;
        $stock->stock = $request->stock;
        $stock->user_id = $user->id;
        
        $stock->save();
        $menu = Menu::find($request->itemid);
        $menu->stock = intval($menu->stock)+($request->stock);
        $menu->save();
        $request->session()->flash('status','Stock saved successfully');
        return redirect('/inventory/stock');
    }

    /**
 * Display the specified resource.
 *
 * @param  int  $id
 * @return \Illuminate\Http\Response
 */
public function show($id)
{
    try {
        $menu = Menu::find($id);
        
        // Check if menu exists
        if (!$menu) {
            return redirect()->back()->with('error', 'Item not found. It may have been deleted.');
        }
        
        return view('inventory.stockDetail')->with('menu', $menu);
    } catch (\Exception $e) {
        \Log::error('Error showing stock detail: ' . $e->getMessage());
        return redirect()->back()->with('error', 'An error occurred while retrieving the item details.');
    }
}

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $user = Auth::user();
        $stock = new InStock();
        $stock->menu_id  = $request->itemid;
        $stock->stock = -intval($request->stock);
        $stock->user_id = $user->id;
        $stock->save();
    
        $menu = Menu::find($request->itemid);
        $menu->stock = intval($menu->stock)-($request->stock);
        $menu->save();
        $request->session()->flash('warning','Stock has been removed successfully');
    
        return redirect('/inventory/stock');
    }
}