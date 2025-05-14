<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KitchenOrder;
use App\Models\KitchenOrderItem;
use App\Models\KitchenEvent;
use App\Models\Sale;
use App\Models\Menu;
use App\Models\SaleDetail;
use App\Models\Table;
use App\Models\Booking;
use App\Models\FoodMenu;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KitchenOrderController extends Controller
{
    /**
     * Display the kitchen order dashboard
     */
  public function index()
    {
        // Manually sync active tables from cashier system to kitchen
        $this->syncCashierToKitchen();
        
        // Get active orders
        $activeOrders = KitchenOrder::with('items')
            ->where('status', '!=', 'completed')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
            
        // Get yesterday's events
        $yesterdayBookings = $this->getBookingsByDate(Carbon::yesterday());
            
        // Get today's events
        $todayBookings = $this->getBookingsByDate(Carbon::today());
            
        // Get tomorrow's events
        $tomorrowBookings = $this->getBookingsByDate(Carbon::tomorrow());
        
        // Get bookings for the next 4 days
        $day3Bookings = $this->getBookingsByDate(Carbon::today()->addDays(2));
        $day4Bookings = $this->getBookingsByDate(Carbon::today()->addDays(3));
        $day5Bookings = $this->getBookingsByDate(Carbon::today()->addDays(4));
        $day6Bookings = $this->getBookingsByDate(Carbon::today()->addDays(5));
            
        return view('kitchen.index', compact(
            'activeOrders', 
            'yesterdayBookings',
            'todayBookings', 
            'tomorrowBookings',
            'day3Bookings',
            'day4Bookings',
            'day5Bookings',
            'day6Bookings'
        ));
    }
    
    
    /**
     * Get today's bookings with food menus
     */
    /**
 * Get today's bookings with food menus
 */
private function getTodayBookings()
{
    $today = Carbon::today();
    
    // Find all bookings for today
    $bookings = Booking::where(function($query) use ($today) {
        $query->whereDate('start', '<=', $today)
              ->whereDate('end', '>=', $today)
              ->whereNotNull('end');
              
        $query->orWhere(function($q) use ($today) {
            $q->whereDate('start', $today)
              ->where(function($sq) {
                  $sq->whereNull('end')
                     ->orWhere('end', 'N/A')
                     ->orWhere('end', '');
              });
        });
    })->get();
    
    // Load food menus for each booking
    $bookings->each(function($booking) use ($today) {
        $booking->menu = FoodMenu::where('booking_id', $booking->id)
                                ->where('date', $today->format('Y-m-d'))
                                ->first();
        return $booking;
    });
    
    return $bookings;
}

/**
 * Get tomorrow's bookings with food menus
 */
private function getTomorrowBookings()
{
    $tomorrow = Carbon::tomorrow();
    
    // Find all bookings for tomorrow
    $bookings = Booking::where(function($query) use ($tomorrow) {
        $query->whereDate('start', '<=', $tomorrow)
              ->whereDate('end', '>=', $tomorrow)
              ->whereNotNull('end');
              
        $query->orWhere(function($q) use ($tomorrow) {
            $q->whereDate('start', $tomorrow)
              ->where(function($sq) {
                  $sq->whereNull('end')
                     ->orWhere('end', 'N/A')
                     ->orWhere('end', '');
              });
        });
    })->get();
    
    // Load food menus for each booking
    $bookings->each(function($booking) use ($tomorrow) {
        $booking->menu = FoodMenu::where('booking_id', $booking->id)
                                ->where('date', $tomorrow->format('Y-m-d'))
                                ->first();
        return $booking;
    });
    
    return $bookings;
}


    
    private function syncCashierToKitchen()
    {
        // Get all tables with 'unavailable' status (active tables in cashier)
        $unavailableTables = Table::where('status', 'unavailable')->get();
        
        foreach ($unavailableTables as $table) {
            // Check if there's an unpaid sale for this table
            $sale = Sale::where('table_id', $table->id)
                ->where('sale_status', 'unpaid')
                ->first();
                
            if ($sale) {
                // Check if this sale already has a kitchen order
                $kitchenOrder = KitchenOrder::where('order_id', 'S' . $sale->id)->first();
                
                if (!$kitchenOrder) {
                    // Create new kitchen order for this sale
                    $kitchenOrder = new KitchenOrder();
                    $kitchenOrder->order_id = 'S' . $sale->id;
                    $kitchenOrder->table_id = $sale->table_id;
                    $kitchenOrder->table_name = $table->name ?? $sale->table_name ?? 'Table ' . $sale->table_id;
                    $kitchenOrder->server = $sale->user_name ?? 'Staff';
                    $kitchenOrder->source = 'Restaurant';
                    $kitchenOrder->time = Carbon::parse($sale->created_at)->format('H:i');
                    
                    // Set status based on sale details
                    $hasUnconfirmedItems = SaleDetail::where('sale_id', $sale->id)
                        ->where('status', 'noConfirm')
                        ->exists();
                    $kitchenOrder->status = $hasUnconfirmedItems ? 'NEW' : 'COOKING';
                    
                    $kitchenOrder->save();
                    
                    // Add sale items to kitchen order
                    $saleDetails = SaleDetail::where('sale_id', $sale->id)->get();
                    foreach ($saleDetails as $detail) {
                        $kitchenItem = new KitchenOrderItem();
                        $kitchenItem->kitchen_order_id = $kitchenOrder->id;
                        $kitchenItem->menu_id = $detail->menu_id;
                        
                        // Double check menu name exists, otherwise use safe values
                        $menu = Menu::find($detail->menu_id);
                        $kitchenItem->menu_name = $menu ? $menu->name : ($detail->menu_name ?? 'Item #' . $detail->menu_id);
                        
                        $kitchenItem->qty = $detail->quantity;
                        $kitchenItem->status = $detail->status == 'confirm' ? 'cooking' : 'new';
                        $kitchenItem->save();
                    }
                } else {
                    // Update existing kitchen order
                    $hasUnconfirmedItems = SaleDetail::where('sale_id', $sale->id)
                        ->where('status', 'noConfirm')
                        ->exists();
                    
                    // Only update status if not READY
                    if ($kitchenOrder->status !== 'READY') {
                        $kitchenOrder->status = $hasUnconfirmedItems ? 'NEW' : 'COOKING';
                        $kitchenOrder->save();
                    }
                    
                    // Get current kitchen items
                    $currentItems = KitchenOrderItem::where('kitchen_order_id', $kitchenOrder->id)
                        ->pluck('id', 'menu_id')
                        ->toArray();
                    
                    // Get current sale details
                    $saleDetails = SaleDetail::where('sale_id', $sale->id)->get();
                    
                    // Track processed menu IDs
                    $processedMenuIds = [];
                    
                    // Update or create kitchen items
                    foreach ($saleDetails as $detail) {
                        $menuId = $detail->menu_id;
                        $processedMenuIds[] = $menuId;
                        
                        // Find menu item to get the name
                        $menu = Menu::find($detail->menu_id);
                        $menuName = $menu ? $menu->name : ($detail->menu_name ?? 'Item #' . $detail->menu_id);
                        
                        if (isset($currentItems[$menuId])) {
                            // Update existing item
                            $kitchenItem = KitchenOrderItem::find($currentItems[$menuId]);
                            $kitchenItem->qty = $detail->quantity;
                            $kitchenItem->menu_name = $menuName; // Update menu name
                            
                            // Only update status if not already 'ready'
                            if ($kitchenItem->status !== 'ready') {
                                $kitchenItem->status = $detail->status == 'confirm' ? 'cooking' : 'new';
                            }
                            
                            $kitchenItem->save();
                        } else {
                            // Create new item
                            $kitchenItem = new KitchenOrderItem();
                            $kitchenItem->kitchen_order_id = $kitchenOrder->id;
                            $kitchenItem->menu_id = $detail->menu_id;
                            $kitchenItem->menu_name = $menuName;
                            $kitchenItem->qty = $detail->quantity;
                            $kitchenItem->status = $detail->status == 'confirm' ? 'cooking' : 'new';
                            $kitchenItem->save();
                        }
                    }
                    
                    // Remove items that are no longer in the sale
                    foreach ($currentItems as $menuId => $itemId) {
                        if (!in_array($menuId, $processedMenuIds)) {
                            KitchenOrderItem::destroy($itemId);
                        }
                    }
                }
            }
        }
        
        // Clean up kitchen orders for tables that are now available
        KitchenOrder::where('order_id', 'like', 'S%')
            ->whereNotIn('table_id', $unavailableTables->pluck('id'))
            ->delete();
    }
    
    /**
     * Update an order's status
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $order = KitchenOrder::findOrFail($id);
        $order->status = $request->status;
        $order->save();
        
        // If this is a cashier order (starts with 'S') and status is READY
        if (substr($order->order_id, 0, 1) === 'S' && $request->status === 'READY') {
            $saleId = substr($order->order_id, 1);
            
            // Update all sale details to 'confirm' status
            SaleDetail::where('sale_id', $saleId)
                ->update(['status' => 'confirm']);
        }
        
        return response()->json(['success' => true, 'message' => 'Order status updated successfully']);
    }
    
    /**
     * Update an order item's status
     */
    public function updateItemStatus(Request $request, $id)
    {
        // Find the item in kitchen_order_items
        $item = KitchenOrderItem::find($id);
        
        if (!$item) {
            // Check if this is a sale detail ID instead
            $saleDetail = SaleDetail::find($id);
            
            if ($saleDetail) {
                // Update the sale detail status
                $saleDetail->kitchen_status = $request->status;
                
                // Also update the status field if needed
                if ($request->status === 'ready') {
                    $saleDetail->status = 'confirm';
                }
                
                $saleDetail->save();
                
                // If we have an order_id, check if all items in this order are ready
                if ($request->has('order_id')) {
                    $orderId = $request->order_id;
                    
                    // Find the kitchen order
                    $kitchenOrder = KitchenOrder::find($orderId);
                    
                    if ($kitchenOrder) {
                        $this->updateKitchenOrderStatus($kitchenOrder);
                    } else if (substr($orderId, 0, 1) === 'S') {
                        // This is a cashier order
                        $saleId = substr($orderId, 1);
                        $allReady = SaleDetail::where('sale_id', $saleId)
                            ->where('status', '!=', 'confirm')
                            ->count() === 0;
                            
                        if ($allReady) {
                            // Update all related kitchen orders
                            $kitchenOrder = KitchenOrder::where('order_id', 'S' . $saleId)->first();
                            if ($kitchenOrder) {
                                $kitchenOrder->status = 'READY';
                                $kitchenOrder->save();
                            }
                        }
                    }
                }
                
                return response()->json(['success' => true, 'message' => 'Item status updated successfully']);
            }
            
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }
        
        // Update the kitchen order item
        $item->status = $request->status;
        $item->save();
        
        // Update the parent kitchen order status if needed
        $kitchenOrder = KitchenOrder::find($item->kitchen_order_id);
        if ($kitchenOrder) {
            $this->updateKitchenOrderStatus($kitchenOrder);
        }
        
        return response()->json(['success' => true, 'message' => 'Item status updated successfully']);
    }

    /**
     * Helper method to update kitchen order status based on its items
     */
    private function updateKitchenOrderStatus($kitchenOrder)
    {
        // Check if all items are ready
        $allReady = $kitchenOrder->items()->where('status', '!=', 'ready')->count() === 0;
        
        if ($allReady) {
            $kitchenOrder->status = 'READY';
        } else {
            // Check if any items are cooking
            $anyCooking = $kitchenOrder->items()->where('status', 'cooking')->count() > 0;
            if ($anyCooking) {
                $kitchenOrder->status = 'COOKING';
            } else {
                $kitchenOrder->status = 'NEW';
            }
        }
        
        $kitchenOrder->save();
        
        // If this is a cashier order (starts with 'S'), update the table status
        if (substr($kitchenOrder->order_id, 0, 1) === 'S') {
            $saleId = substr($kitchenOrder->order_id, 1);
            
            // Update all sale details to 'confirm' if the order is ready
            if ($kitchenOrder->status === 'READY') {
                SaleDetail::where('sale_id', $saleId)
                    ->update(['status' => 'confirm']);
            }
        }
        
        return $kitchenOrder;
    }
    
    /**
     * Load orders by filter
     */
    public function getOrdersBySource(Request $request)
    {
        // Refresh sync with cashier system
        $this->syncCashierToKitchen();
        
        $source = $request->source;
        
        $query = KitchenOrder::with('items')
            ->where('status', '!=', 'completed');
            
        if ($source !== 'all') {
            $query->where('source', $source);
        }
        
        $orders = $query->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
            
        return response()->json($orders);
    }
    
    /**
     * Get today's events
     */
    public function getTodayEvents()
    {
        // Get today's bookings with menus instead of KitchenEvent
        $bookings = $this->getTodayBookings();
            
        return response()->json($bookings);
    }
    
    /**
     * Get tomorrow's events
     */
    public function getTomorrowEvents()
    {
        // Get tomorrow's bookings with menus instead of KitchenEvent
        $bookings = $this->getTomorrowBookings();
            
        return response()->json($bookings);
    }

    
    /**
     * Get yesterday's events - Using updated method
     */
    public function getYesterdayEvents()
    {
        $bookings = $this->getBookingsByDate(Carbon::yesterday());
        return response()->json($bookings);
    }

    /**
     * Get events for day 3 - New method
     */
    public function getDay3Events()
    {
        $bookings = $this->getBookingsByDate(Carbon::today()->addDays(2));
        return response()->json($bookings);
    }
    
    /**
     * Get events for day 4 - New method
     */
    public function getDay4Events()
    {
        $bookings = $this->getBookingsByDate(Carbon::today()->addDays(3));
        return response()->json($bookings);
    }
    
    /**
     * Get events for day 5 - New method
     */
    public function getDay5Events()
    {
        $bookings = $this->getBookingsByDate(Carbon::today()->addDays(4));
        return response()->json($bookings);
    }
    
    /**
     * Get events for day 6 - New method
     */
    public function getDay6Events()
    {
        $bookings = $this->getBookingsByDate(Carbon::today()->addDays(5));
        return response()->json($bookings);
    }
    
    


    /**
     * Get menus
     */
    public function getMenus()
    {
        $menus = [];
        
        // This is a placeholder since we don't know your exact menu structure
        // Adapt this to your actual menu data source
        
        return response()->json($menus);
    }
    
    /**
     * Get analytics data
     */
    public function getAnalyticsData()
    {
        // Sample analytics data - replace with real calculations
        $data = [
            'ordersToday' => KitchenOrder::whereDate('created_at', Carbon::today())->count(),
            'ordersTrend' => 15, // Percentage increase from yesterday
            'averagePrepTime' => 18, // Minutes
            'prepTimeTrend' => 2, // Minutes above target,
            'lateOrders' => 5,
            'lateOrdersTrend' => -2 // Change from yesterday
        ];
        
        return response()->json($data);
    }

    /**
     * Get active orders from the cashier system (Sales model)
     */
    private function getActiveCashierOrders()
    {
        // Get all unpaid sales (these correspond to active tables in the cashier system)
        $sales = Sale::where('sale_status', 'unpaid')
            ->with('saleDetails')  // Eager load the sale details
            ->get();
            
        // Transform these sales into the format expected by the kitchen display
        return $sales->map(function($sale) {
            // Get the table info
            $table = Table::find($sale->table_id);
            
            // Create a kitchen order object
            $order = new KitchenOrder();
            $order->id = 'S' . $sale->id; // Prefix with 'S' to distinguish from regular kitchen orders
            $order->order_id = $sale->id;
            $order->table = $sale->table_name;
            $order->server = $sale->user_name;
            $order->guests = 0; // Set a default or get from somewhere if available
            $order->source = 'Restaurant'; // Default source
            $order->time = Carbon::parse($sale->created_at)->format('H:i');
            $order->estimated_complete = Carbon::parse($sale->created_at)->addMinutes(30)->format('H:i'); // Example estimation
            $order->status = 'NEW'; // Default status for cashier orders
            $order->priority = 'normal'; // Default priority
            
            // Transform sale details into kitchen order items
            // Here, instead of creating new KitchenOrderItem instances,
            // we'll include the original sale_details data
            $order->items = $sale->saleDetails->map(function($detail) {
                // Create an object that has both kitchen order item properties
                // and the original sale_detail properties
                $item = new \stdClass();
                $item->id = $detail->id;
                $item->menu_id = $detail->menu_id;
                $item->menu_name = $detail->menu_name;
                $item->qty = $detail->quantity;
                $item->notes = ''; // Add notes if available
                $item->status = $detail->status == 'confirm' ? 'cooking' : 'new';
                $item->progress_percentage = $detail->status == 'confirm' ? 50 : 0;
                $item->progress_color = $detail->status == 'confirm' ? 'bg-yellow-500' : 'bg-gray-400';
                
                // Include the original sale_detail data
                $item->original_detail = $detail;
                $item->created_at = $detail->created_at;
                $item->updated_at = $detail->updated_at;
                
                return $item;
            })->toArray();
            
            return $order;
        });
    }



    /**
 * Get bookings with food menus for a specific date
 * 
 * @param \Carbon\Carbon $date
 * @return \Illuminate\Database\Eloquent\Collection
 */
private function getBookingsByDate(Carbon $date)
{
    // Find all bookings for the given date
    $bookings = Booking::where(function($query) use ($date) {
        $query->whereDate('start', '<=', $date)
              ->whereDate('end', '>=', $date)
              ->whereNotNull('end');
              
        $query->orWhere(function($q) use ($date) {
            $q->whereDate('start', $date)
              ->where(function($sq) {
                  $sq->whereNull('end')
                     ->orWhere('end', 'N/A')
                     ->orWhere('end', '');
              });
        });
    })->get();
    
    // Load food menus for each booking
    $bookings->each(function($booking) use ($date) {
        $booking->menu = FoodMenu::where('booking_id', $booking->id)
                                ->where('date', $date->format('Y-m-d'))
                                ->first();
        return $booking;
    });
    
    return $bookings;
}

    /**
     * Update an item's status directly
     * This method is for API calls from the kitchen display
     */
    public function updateStatus(Request $request, $id)
    {
        // Find the item
        $item = KitchenOrderItem::find($id);
        
        if (!$item) {
            // If item not found, check if it's a sale detail ID
            $saleDetail = SaleDetail::find($id);
            
            if (!$saleDetail) {
                return response()->json(['success' => false, 'message' => 'Item not found'], 404);
            }
            
            // Update the sale detail status
            $saleDetail->status = $request->status === 'ready' ? 'confirm' : 'cooking';
            $result = $saleDetail->save();
            
            // Add logging for debugging
            Log::info('Sale detail status updated', [
                'item_id' => $id,
                'new_status' => $request->status,
                'result' => $result,
                'timestamp' => Carbon::now()->toDateTimeString()
            ]);
            
            return response()->json(['success' => true, 'message' => 'Item status updated successfully']);
        }
        
        // Update the kitchen order item status
        $oldStatus = $item->status;
        $item->status = $request->status;
        $result = $item->save();
        
        // If order_id is provided, update the parent order's status
        if ($request->has('order_id')) {
            $orderId = $request->order_id;
            $order = KitchenOrder::find($orderId);
            
            if ($order) {
                $this->updateOrderStatus(new Request(['status' => $this->determineOrderStatus($order)]), $orderId);
            }
        }
        
        // Add logging for debugging
        Log::info('Item status updated', [
            'item_id' => $id,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'result' => $result,
            'timestamp' => Carbon::now()->toDateTimeString()
        ]);
        
        return response()->json(['success' => true, 'message' => 'Item status updated successfully']);
    }
    
    /**
     * Determine the status of an order based on its items
     */
    private function determineOrderStatus($order)
    {
        // Get a fresh count of items by status
        $items = KitchenOrderItem::where('kitchen_order_id', $order->id)->get();
        
        if ($items->isEmpty()) {
            return 'NEW';
        }
        
        // If all items are ready, order is ready
        if ($items->every(function($item) { return $item->status === 'ready'; })) {
            return 'READY';
        }
        
        // If any items are cooking, order is cooking
        if ($items->contains(function($item) { return $item->status === 'cooking'; })) {
            return 'COOKING';
        }
        
        // Otherwise, order is new
        return 'NEW';
    }
}