<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Table;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\InStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CashierController extends Controller
{
    /**
     * OPTIMIZED: Cache categories for 1 hour (they rarely change)
     */
    public function index() {
        $categories = Cache::remember('cashier_categories', 3600, function () {
            return Category::all();
        });
        return view('cashier.index')->with('categories', $categories);
    }

    /**
     * OPTIMIZED: Cache tables for 30 seconds (status changes frequently)
     */
    public function getTables(){
        // Disable cache to ensure UI updates are seen immediately
        Cache::forget('cashier_tables');
        $tables = Table::all();
        
        // Container with flexbox grid - using inline styles to force layout
        $html = '<div style="display: flex; flex-wrap: wrap; gap: 6px; padding: 8px;">';
        
        foreach($tables as $table){
            $isAvailable = $table->status == "available";
            $statusClass = $isAvailable ? 'available' : 'occupied';
            
            // Colors matching the online screenshot
            // Available: Green/Teal theme
            // Occupied: Orange/Yellow theme
            $themeColor = $isAvailable ? '#00b894' : '#fdcb6e'; // Mint green vs Mustard yellow
            if (!$isAvailable) $themeColor = '#ffa502'; // Darker orange for occupied
            
            $statusText = $isAvailable ? 'AVAILABLE' : 'OCCUPIED';
            
            // Table Card - White background, colored border
            $html .= '<div class="btn-table table-card '.$statusClass.'" data-id="'.$table->id.'" data-name="'.$table->name.'" style="
                width: 85px;
                height: 85px;
                border: 2px solid '.$themeColor.';
                border-radius: 10px;
                background-color: #ffffff;
                margin: 2px;
                cursor: pointer;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                transition: transform 0.2s;
                position: relative;
            " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'">';
            
            // Name
            $html .= '<div class="table-name" style="font-weight: 700; color: #2d3436; font-size: 11px; margin-bottom: 4px; text-align: center; line-height: 1.1;">'.$table->name.'</div>';
            
            // Status Badge
            $html .= '<div class="table-status" style="
                background-color: '.$themeColor.';
                color: white;
                padding: 2px 8px;
                border-radius: 10px;
                font-size: 8px;
                text-transform: uppercase;
                font-weight: 700;
                letter-spacing: 0.5px;
            ">'.$statusText.'</div>';
            
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }
    
    /**
     * OPTIMIZED: Better query for menu search
     */
    public function getMenuByCategory($category_id,$search_keyword = ''){
        if($category_id == 0){
            $menus = Menu::where('name', 'LIKE', "%{$search_keyword}%")->get();
        }else{
            // OPTIMIZED: Cache menus by category for 5 minutes
            $menus = Cache::remember('menus_category_' . $category_id, 300, function () use ($category_id) {
                return Menu::where('category_id', $category_id)->get();
            });
        }
        $html = '';
        foreach($menus as $menu){
            $html .= '
            <div class="col-md-auto  ml-1  mt-3 " >
                <a class="btn  btn-outline-success btn-light  btn-menu  "  data-id="'.$menu->id.'">
                    <br>
                    '.$menu->name.'
                    <br>
                    Rs'.number_format($menu->price).'
                </a>
            </div>';
        }
        return $html;
    }

    public function orderFood(Request $request){
        $menu = Menu::find($request->menu_id);
        $table_id = $request->table_id;
        $table_name = $request->table_name;
        $sale = Sale::where('table_id', $table_id)->where('sale_status','unpaid')->first();
        
        $tableStatusChanged = false;
        
        if(!$sale){
            $user = Auth::user();
            $sale = new Sale();
            $sale->table_id = $table_id;
            $sale->table_name = $table_name;
            $sale->user_id = $user->id;
            $sale->user_name = $user->name;
            $sale->total_price = 0; // Initialize to 0
            $sale->save();
            $sale_id = $sale->id;
            
            $table = Table::find($table_id);
            $table->status = "unavailable";
            $table->save();
            
            // OPTIMIZED: Clear table cache when status changes
            Cache::forget('cashier_tables');
            
            $tableStatusChanged = true;
        }else{
            $sale_id = $sale->id;
        }

        $saleDetail = new SaleDetail();
        $saleDetail->sale_id = $sale_id;
        $saleDetail->menu_id = $menu->id;
        $saleDetail->menu_name = $menu->name;
        $saleDetail->menu_price = $menu->price;
        $saleDetail->quantity = $request->quantity;
        $saleDetail->count = 1;
        $saleDetail->save();

        // FIXED: Properly recalculate total instead of just adding
        $this->recalculateSaleTotal($sale_id);

        return [
            'html' => $this->getSaleDetails($sale_id),
            'tableStatusChanged' => $tableStatusChanged,
            'tableId' => $table_id
        ];
    }

    public function getSaleDetailsByTable($table_id){
        // Optimize: Use eager loading to fetch sale with details in one query
        $sale = Sale::where('table_id', $table_id)
            ->where('sale_status','unpaid')
            ->with('saleDetails')
            ->first();
        
        $html = '';
        if($sale){
            $sale_id = $sale->id;
            $html .= $this->getSaleDetails($sale_id);
        }else{
            $html .= "Not Found Any Sale Details for the Selected Table";
        }
        return $html;
    }

    /**
     * OPTIMIZED: Load sale with details in single query
     */
    private function getSaleDetails($sale_id){
        $html = '<p>Sale ID: '.$sale_id.'</p>';
        $saleDetails = SaleDetail::where('sale_id', $sale_id)->get();
        $html .= '<div class="table-responsive-md" tabindex ="-1" style="overflow-y:scroll; min-height: 400px; border: 1px solid #343A40">
        <table class="table table-stripped table-dark">
        <thead>
            <tr>
                <th scope="col">Menu</th>
                <th scope="col">Quantity</th>
                <th scope="col">Price</th>
                <th scope="col">Total</th>
                <th scope="col">Updated Time</th>
                <th scope="col">Status</th>
            </tr>
        </thead>
        <tbody>';
        
        $showBtnPayment = true;
        $hasAdvancePayment = false;
        $hasRegularMenuItems = false;
        $totalItemsAmount = 0; // Track actual items total
        
        foreach($saleDetails as $saleDetail){
            // Check if this is an advance payment item
            if (strpos($saleDetail->menu_name, 'Advance Payment') !== false) {
                $hasAdvancePayment = true;
            } else {
                $hasRegularMenuItems = true;
            }
            
            $itemTotal = $saleDetail->menu_price * $saleDetail->quantity;
            $totalItemsAmount += $itemTotal; // Add to running total
            
            $updatedDateTime = $saleDetail->created_at ? $saleDetail->created_at->format('d/m/Y H:i:s') : '';
            $html .= '
            <tr>
                <td>'.$saleDetail->menu_name.'</td>
                <td><input type="number" tabindex ="-1" class="change-quantity" data-id="'.$saleDetail->id.'" 
                           style="width:50px;" value="'.$saleDetail->quantity.'"'.
                           ($saleDetail->status == "confirm" ? ' disabled' : '').'></td>
                <td>'.$saleDetail->menu_price.'</td>
                <td>'.number_format($itemTotal, 2).'</td>
                <td>'.$updatedDateTime.'</td>';
                if($saleDetail->status == "noConfirm"){
                    $showBtnPayment = false;
                    $html .= '<td><a data-id="'.$saleDetail->id.'" class="btn btn-danger btn-delete-saledetail"><i class="far fa-trash-alt"></a></td>';
                }else{
                    $adminUser = Auth::user();
                    if ($adminUser && $adminUser->role === 'admin') {
                        $html .= '<td><i class="fas fa-check-circle me-1"></i><a data-id="'.$saleDetail->id.'" class="btn btn-outline-danger btn-sm btn-admin-delete-confirmed" style="padding:2px 6px; font-size:0.7rem;" title="Admin: Remove item"><i class="fas fa-times"></i></a></td>';
                    } else {
                        $html .= '<td><i class="fas fa-check-circle"></i></td>';
                    }
                }
            $html .= '</tr>';
        }
        $html .='</tbody></table></div>';

        // Get sale record 
        $sale = Sale::find($sale_id);
        
        // CRITICAL FIX: Always ensure sale total_price matches the sum of all items
        if ($sale && abs($sale->total_price - $totalItemsAmount) > 0.01) {
            $oldTotal = $sale->total_price;
            $sale->total_price = $totalItemsAmount;
            $sale->save();
            
            // Log this correction
            \Log::warning('Sale total corrected in getSaleDetails', [
                'sale_id' => $sale_id,
                'old_total' => $oldTotal,
                'new_total' => $totalItemsAmount,
                'difference' => abs($oldTotal - $totalItemsAmount)
            ]);
        }

        $html .= '<hr>';
        $html .= '<h3>Total Amount: Rs '.number_format($sale->total_price, 2).'</h3>';

        if($showBtnPayment){
            $html .= '<button data-id="'.$sale_id.'" data-totalAmount="'.$sale->total_price.'" class="btn btn-success btn-block btn-payment" data-bs-toggle="modal" data-bs-target="#exampleModal">Payment</button>';
            $html .= '<button data-id="'.$sale_id.'" class="btn btn-dark btn-block btn-payment printKot">Print KOT</button>';
            
            // Only show advance payment button if there are no regular menu items AND no existing advance payment
            if (!$hasRegularMenuItems && !$hasAdvancePayment) {
                $html .= '<a href="'.url('/cashier/advance-payment/'.$sale_id).'" class="btn btn-primary btn-block">Advance Payment</a>';
            }

            // Admin-only: Clear Table button
            $currentUser = Auth::user();
            if ($currentUser && $currentUser->role === 'admin') {
                $html .= '<button data-id="'.$sale_id.'" class="btn btn-outline-danger btn-block btn-clear-table mt-2" onclick="clearTable('.$sale_id.')"><i class="fas fa-broom me-1"></i> Clear Table (Admin)</button>';
            }
        }else{
            $html .= '<button data-id="'.$sale_id.'" class="btn btn-warning btn-block btn-confirm-order">Confirm Order</button>';
        }
        return $html;
    }

    public function increaseQuantity(Request $request){
        $saleDetail_id = $request->saleDetail_id;
        $saleDetail = SaleDetail::where('id',$saleDetail_id)->first();
        $saleDetail->quantity = $saleDetail->quantity + 1;
        $saleDetail->save();
        
        // FIXED: Recalculate total properly
        $this->recalculateSaleTotal($saleDetail->sale_id);
        
        return $this->getSaleDetails($saleDetail->sale_id);
    }

    public function changesQuantity(Request $request){
        $saleDetail_id = $request->saleDetail_id;
        $qty = $request->qty;
        $saleDetail = SaleDetail::where('id',$saleDetail_id)->first();

        // Update quantity
        $saleDetail->quantity = $qty;
        $saleDetail->save();
        
        // FIXED: Recalculate total properly
        $this->recalculateSaleTotal($saleDetail->sale_id);
        
        return $this->getSaleDetails($saleDetail->sale_id);
    }

    public function decreaseQuantity(Request $request){
        $saleDetail_id = $request->saleDetail_id;
        $saleDetail = SaleDetail::where('id',$saleDetail_id)->first();
        
        if($saleDetail->quantity > 1) {
            $saleDetail->quantity = $saleDetail->quantity - 1;
            $saleDetail->save();
        }
        
        // FIXED: Recalculate total properly
        $this->recalculateSaleTotal($saleDetail->sale_id);
        
        return $this->getSaleDetails($saleDetail->sale_id);
    }

    public function confirmOrderStatus(Request $request) {
        $sale_id = $request->sale_id;
        
        // Get existing quantities before update
        $saleDetails = SaleDetail::where('sale_id', $sale_id)->get();
        
        // Update status first
        SaleDetail::where('sale_id', $sale_id)->update(['status' => 'confirm']);
        
        // Update count while preserving quantity
        foreach ($saleDetails as $detail) {
            SaleDetail::where('id', $detail->id)->update([
                'count' => $detail->count + 1,
                'quantity' => $detail->quantity  // Explicitly preserve quantity
            ]);
        }
        
        return $this->getSaleDetails($sale_id);
    }

    /**
     * Admin-only: Delete a confirmed sale detail item
     */
    public function adminDeleteConfirmedItem(Request $request){
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized. Admin access required.'], 403);
        }

        $saleDetail = SaleDetail::find($request->saleDetail_id);
        if (!$saleDetail) {
            return 'Item not found.';
        }

        $sale_id = $saleDetail->sale_id;
        $itemName = $saleDetail->menu_name;
        $itemQty = $saleDetail->quantity;

        $saleDetail->delete();
        $this->recalculateSaleTotal($sale_id);

        // Log the action
        try {
            DB::table('menu_activity_logs')->insert([
                'action' => 'confirmed_item_removed',
                'menu_id' => null,
                'menu_name' => $itemName,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'details' => "Admin removed confirmed item: {$itemName} x{$itemQty} from Sale #{$sale_id}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {}

        // Check if any items remain
        $remaining = SaleDetail::where('sale_id', $sale_id)->first();
        if ($remaining) {
            return $this->getSaleDetails($sale_id);
        } else {
            // No items left — free the table
            $sale = Sale::find($sale_id);
            if ($sale) {
                $sale->sale_status = 'cancelled';
                $sale->total_price = 0;
                $sale->save();
                $table = Table::find($sale->table_id);
                if ($table) {
                    $table->status = 'available';
                    $table->save();
                    Cache::forget('cashier_tables');
                }
            }
            return "Not Found Any Sale Details for the Selected Table";
        }
    }

    public function deleteSaleDetail(Request $request){
        $saleDetail_id = $request->saleDetail_id;
        $saleDetail = SaleDetail::find($saleDetail_id);
        $sale_id = $saleDetail->sale_id;
        
        // Delete the item
        $saleDetail->delete();

        // FIXED: Recalculate total properly
        $this->recalculateSaleTotal($sale_id);
        
        $saleDetails = SaleDetail::where('sale_id', $sale_id)->first();
        if($saleDetails){
            $html = $this->getSaleDetails($sale_id);
        }else{
            $html = "Not Found Any Sale Details for the Selected Table";
        }
        return $html;
    }

    // NEW METHOD: Properly recalculate sale totals
    private function recalculateSaleTotal($sale_id) {
        try {
            // Get all sale details for this sale
            $saleDetails = SaleDetail::where('sale_id', $sale_id)->get();
            
            // Calculate correct total
            $correctTotal = $saleDetails->sum(function($detail) {
                return $detail->menu_price * $detail->quantity;
            });
            
            // Update the sale record
            $sale = Sale::find($sale_id);
            if ($sale) {
                $sale->total_price = $correctTotal;
                $sale->save();
                
                \Log::info('Sale total recalculated', [
                    'sale_id' => $sale_id,
                    'new_total' => $correctTotal,
                    'items_count' => $saleDetails->count()
                ]);
            }
            
            return $correctTotal;
        } catch (\Exception $e) {
            \Log::error('Error recalculating sale total: ' . $e->getMessage(), [
                'sale_id' => $sale_id,
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }

    public function savePayment(Request $request){
        // Validate all payment inputs to prevent manipulation via DevTools
        $validated = $request->validate([
            'saleID' => 'required|integer|exists:sales,id',
            'recievedAmount' => 'required|numeric|min:0|max:999999',
            'PaymentType' => 'required|in:cash,card,bank_transfer,online'
        ]);
        
        $saleID = $validated['saleID'];
        $recievedAmount = $validated['recievedAmount'];
        $paymentType = $validated['PaymentType'];
        
        // Begin transaction for data consistency
        DB::beginTransaction();
        
        try {
            $sale = Sale::findOrFail($saleID);
            
            // Security: Log suspicious low or zero service charges
            // Normal service charge should be at least 2% of bill
            $expectedMinimum = $sale->total_price * 0.02;
            if ($recievedAmount < $expectedMinimum && $sale->total_price > 0) {
                \Log::warning('SUSPICIOUS: Low service charge detected', [
                    'user_id' => Auth::id(),
                    'user_name' => Auth::user()->name,
                    'sale_id' => $saleID,
                    'bill_amount' => $sale->total_price,
                    'service_charge_entered' => $recievedAmount,
                    'expected_minimum' => $expectedMinimum,
                    'table' => $sale->table_name,
                    'timestamp' => now()
                ]);
            }
            $sale->total_recieved = $recievedAmount;
            
            // ⚠️ IMPORTANT: DO NOT MODIFY THIS CALCULATION - IT IS CORRECT!
            // The 'change' field name is MISLEADING - it does NOT store customer change.
            // ACTUAL PURPOSE: Stores Grand Total = Service Charge + Bill Total
            // Formula: change = total_recieved (service charge) + total_price (bill)
            // This is INTENTIONAL business logic from original system design.
            // Example: Bill Rs.5000 + Service Rs.500 = Grand Total Rs.5500 (stored in 'change')
            $sale->change = $recievedAmount + $sale->total_price;
            
            $sale->payment_type = $paymentType;
            $sale->sale_status = "paid";
            $sale->save();
            
            $table = Table::find($sale->table_id);
            $table->status = "available";
            $table->save();
            
            // OPTIMIZED: Clear table cache when status changes
            Cache::forget('cashier_tables');
            
            // Only reduce stock if it hasn't been reduced yet
            if (!$this->hasStockBeenReduced($saleID)) {
                $saleDetail = SaleDetail::where('sale_id', $saleID)->get();
                
                foreach ($saleDetail as $value) {
                    $user = Auth::user();
                    $stock = new InStock();
                    $stock->menu_id = $value->menu_id;
                    $stock->stock = -intval($value->quantity);
                    $stock->user_id = $user->id;
                    $stock->sale_id = $saleID;
                    $stock->save();
        
                    $menu = Menu::find($value->menu_id);
                    $menu->stock = intval($menu->stock) - ($value->quantity);
                    $menu->save();     
                }
                
                // FIXED: Automatically deduct kitchen ingredients for ALL recipe items
                $this->processKitchenStockDeduction($saleID);
            }
            
            DB::commit();
            return url('/cashier/showRecipt')."/".$saleID;
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error in savePayment: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing payment.'], 500);
        }
    }

    public function showRecipt($saleID){
        $sale = Sale::find($saleID);
        $saleDetails = SaleDetail::where('sale_id', $saleID)->get();
        return view('cashier.showRecipt')->with('sale',$sale)->with('saleDetails', $saleDetails);
    }

    public function printOrder(Request $request){
        return url('/cashier/printOrderRec')."/".$request->saleID;
    }

    public function printOrderRec($saleID){
        $sale = Sale::find($saleID);
        $saleDetails = SaleDetail::get()->where('sale_id',$saleID)->where('count',2);
        return view('cashier.printOrder')->with('sale',$sale)->with('saleDetails', $saleDetails);
    }

    /**
     * Check if stock has already been reduced for this sale
     * 
     * @param int $saleId
     * @return bool
     */
    private function hasStockBeenReduced($saleId)
    {
        // Look for stock reduction records related to this sale
        $count = \App\Models\InStock::where('sale_id', $saleId)
            ->where('stock', '<', 0)
            ->count();
        
        return $count > 0;
    }

    public function showAdvanceRecipt($saleID){
        $sale = Sale::find($saleID);
        $saleDetails = SaleDetail::where('sale_id', $saleID)->get();
        return view('cashier.showAdvanceRecipt')->with('sale',$sale)->with('saleDetails', $saleDetails);
    }

    public function printAdvanceRecipt($saleID){
        $sale = Sale::find($saleID);
        $saleDetails = SaleDetail::where('sale_id', $saleID)->get();
        return view('cashier.printAdvanceRecipt')->with('sale',$sale)->with('saleDetails', $saleDetails);
    }

    public function showAdvanceWeddingRecipt($saleID){
        $sale = Sale::find($saleID);
        $saleDetails = SaleDetail::where('sale_id', $saleID)->get();
        return view('cashier.showAdvanceWeddingRecipt')->with('sale',$sale)->with('saleDetails', $saleDetails);
    }

    /**
     * Show the advance payment selection page (Wedding or Function)
     * 
     * @param int $saleID The sale ID
     * @return \Illuminate\Http\Response
     */
    public function showAdvancePaymentSelection($saleID)
    {
        $sale = Sale::find($saleID);
        
        if (!$sale) {
            return redirect()->back()->with('error', 'Sale not found.');
        }
        
        return view('cashier.advancePaymentSelection', compact('sale'));
    }

    /**
     * Show the advance payment form
     * 
     * @param int $saleID The sale ID
     * @param string $type The payment type (wedding or function)
     * @return \Illuminate\Http\Response
     */
    public function showAdvancePaymentForm($saleID, $type)
    {
        $sale = Sale::find($saleID);
        
        if (!$sale) {
            return redirect()->back()->with('error', 'Sale not found.');
        }
        
        if (!in_array($type, ['wedding', 'function'])) {
            return redirect()->back()->with('error', 'Invalid payment type.');
        }
        
        return view('cashier.advancePaymentForm', compact('sale', 'type'));
    }

    /**
     * Process the advance payment form submission
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function submitAdvancePayment(Request $request)
    {
        // Validate the request
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'amount' => 'required|numeric|min:1',
            'payment_type' => 'required|in:function,wedding',
            'description' => 'nullable|string|max:255',
        ]);

        // Get the sale
        $sale = Sale::find($request->sale_id);
        
        // Create a new menu item for the advance payment if not exists
        $menuName = 'Advance Payment - ' . ($request->payment_type == 'wedding' ? 'Wedding' : 'Function');
        if (!empty($request->description)) {
            $menuName .= ' - ' . $request->description;
        }
        
        $menu = Menu::firstOrCreate(
            ['name' => $menuName],
            [
                'price' => 0, // Price will be set per transaction
                'description' => 'System generated advance payment',
                'category_id' => 185, // Using category ID 185 as requested
                'image' => 'noimage.png'
            ]
        );
        
        // Delete any existing sale details for this sale (in case user is updating)
        SaleDetail::where('sale_id', $sale->id)->delete();
        
        // Create a new sale detail
        $saleDetail = new SaleDetail();
        $saleDetail->sale_id = $sale->id;
        $saleDetail->menu_id = $menu->id;
        $saleDetail->menu_name = $menuName;
        $saleDetail->menu_price = $request->amount;
        $saleDetail->quantity = 1;
        $saleDetail->count = 1;
        $saleDetail->status = 'confirm';
        $saleDetail->save();
        
        // Update the sale
        $sale->total_price = $request->amount;
        $sale->total_recieved = 0; // No service charge for advance payments
        $sale->change = $request->amount; // Total amount for the receipt
        $sale->payment_type = $request->payment_type;
        $sale->sale_status = "paid"; // Mark the sale as paid
        $sale->save();
        
        // Set the table back to available
        $table = Table::find($sale->table_id);
        if ($table) {
            $table->status = "available";
            $table->save();
            
            // OPTIMIZED: Clear table cache when status changes
            Cache::forget('cashier_tables');
        }
        
        // Redirect to the appropriate receipt page based on payment type
        if ($request->payment_type == 'wedding') {
            return redirect()->to('/cashier/showAdvanceWeddingRecipt/' . $sale->id);
        } else {
            return redirect()->to('/cashier/showAdvanceRecipt/' . $sale->id);
        }
    }

    /**
     * Set up a new sale for advance payment
     *
     * @param int $table_id The table ID
     * @return \Illuminate\Http\Response
     */
    public function setupAdvancePayment($table_id)
    {
        $table = Table::find($table_id);
        
        if (!$table) {
            return redirect()->back()->with('error', 'Table not found.');
        }
        
        // Check if there's already a sale for this table
        $existingSale = Sale::where('table_id', $table_id)
            ->where('sale_status', 'unpaid')
            ->first();
        
        if ($existingSale) {
            // If a sale exists, redirect to the advance payment selection
            return redirect('/cashier/advance-payment/' . $existingSale->id);
        }
        
        // Create a new sale
        $user = Auth::user();
        $sale = new Sale();
        $sale->table_id = $table_id;
        $sale->table_name = $table->name;
        $sale->user_id = $user->id;
        $sale->user_name = $user->name;
        $sale->total_price = 0;
        $sale->save();
        
        // Update table status
        $table->status = "unavailable";
        $table->save();
        
        // OPTIMIZED: Clear table cache when status changes
        Cache::forget('cashier_tables');
        
        // Redirect to the advance payment selection
        return redirect('/cashier/advance-payment/' . $sale->id);
    }

    /**
     * Admin-only: Clear table - remove all items and free the table
     */
    public function clearTable(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized. Admin access required.'], 403);
        }

        $request->validate(['sale_id' => 'required|integer']);
        $sale = Sale::find($request->sale_id);

        if (!$sale) {
            return response()->json(['error' => 'Sale not found.'], 404);
        }

        DB::beginTransaction();
        try {
            // Delete all sale details
            SaleDetail::where('sale_id', $sale->id)->delete();

            // Mark sale as cancelled
            $sale->sale_status = 'cancelled';
            $sale->total_price = 0;
            $sale->save();

            // Free the table
            $table = Table::find($sale->table_id);
            if ($table) {
                $table->status = 'available';
                $table->save();
                Cache::forget('cashier_tables');
            }

            // Log the action
            DB::table('menu_activity_logs')->insert([
                'action' => 'table_cleared',
                'menu_id' => null,
                'menu_name' => null,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'details' => "Cleared table {$sale->table_name} (Sale #{$sale->id}) by admin",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => "Table {$sale->table_name} cleared successfully."]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error clearing table: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to clear table.'], 500);
        }
    }

    private function processKitchenStockDeduction($saleID)
    {
        try {
            \Log::info("Starting kitchen stock deduction for sale #{$saleID}");
            
            // Get sale details
            $saleDetails = SaleDetail::where('sale_id', $saleID)->get();
            
            \Log::info("Found " . $saleDetails->count() . " sale items to process");

            foreach ($saleDetails as $saleDetail) {
                \Log::info("Processing menu item: {$saleDetail->menu_name} (ID: {$saleDetail->menu_id}) x{$saleDetail->quantity}");
                
                // Get ALL recipes for this menu item - FIXED: Ensure we get all ingredients
                $recipes = DB::table('menu_item_recipes')
                    ->join('items', 'menu_item_recipes.item_id', '=', 'items.id')
                    ->where('menu_item_recipes.menu_id', $saleDetail->menu_id)
                    ->where('items.is_kitchen_item', true)
                    ->where('items.kitchen_is_active', true)
                    ->select(
                        'menu_item_recipes.*',
                        'items.name as item_name',
                        'items.kitchen_current_stock',
                        'items.kitchen_unit',
                        'items.kitchen_cost_per_unit'
                    )
                    ->get();

                \Log::info("Found " . $recipes->count() . " recipe ingredients for {$saleDetail->menu_name}");

                if ($recipes->isEmpty()) {
                    \Log::warning("No recipe found for menu item: {$saleDetail->menu_name} (ID: {$saleDetail->menu_id})");
                    continue;
                }

                // Process EACH ingredient in the recipe
                foreach ($recipes as $recipe) {
                    $totalRequired = $recipe->required_quantity * $saleDetail->quantity;
                    
                    \Log::info("Processing ingredient: {$recipe->item_name}", [
                        'required_per_item' => $recipe->required_quantity,
                        'sale_quantity' => $saleDetail->quantity,
                        'total_required' => $totalRequired,
                        'current_stock' => $recipe->kitchen_current_stock
                    ]);
                    
                    // Check if we have enough stock
                    if ($recipe->kitchen_current_stock >= $totalRequired) {
                        // Deduct from kitchen stock
                        $oldStock = $recipe->kitchen_current_stock;
                        $newStock = $oldStock - $totalRequired;

                        // Update kitchen stock
                        $updateResult = DB::table('items')
                            ->where('id', $recipe->item_id)
                            ->update([
                                'kitchen_current_stock' => $newStock,
                                'updated_at' => now()
                            ]);

                        if ($updateResult) {
                            // Log the consumption in kitchen_stock_logs
                            DB::table('kitchen_stock_logs')->insert([
                                'item_id' => $recipe->item_id,
                                'action' => 'menu_consumption',
                                'quantity_before' => $oldStock,
                                'quantity_change' => -$totalRequired,
                                'quantity_after' => $newStock,
                                'description' => "Auto: {$saleDetail->menu_name} x{$saleDetail->quantity} (Sale #{$saleID})",
                                'user_id' => Auth::id(),
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);

                            \Log::info("Successfully deducted kitchen stock", [
                                'ingredient' => $recipe->item_name,
                                'old_stock' => $oldStock,
                                'deducted' => $totalRequired,
                                'new_stock' => $newStock,
                                'unit' => $recipe->kitchen_unit
                            ]);
                        } else {
                            \Log::error("Failed to update kitchen stock for item ID: {$recipe->item_id}");
                        }
                    } else {
                        // Log insufficient stock warning but don't stop the sale
                        \Log::warning("Insufficient kitchen stock for auto-deduction", [
                            'sale_id' => $saleID,
                            'menu_item' => $saleDetail->menu_name,
                            'ingredient' => $recipe->item_name,
                            'required' => $totalRequired,
                            'available' => $recipe->kitchen_current_stock,
                            'unit' => $recipe->kitchen_unit
                        ]);
                        
                        // Still log the attempted consumption for tracking
                        DB::table('kitchen_stock_logs')->insert([
                            'item_id' => $recipe->item_id,
                            'action' => 'insufficient_stock',
                            'quantity_before' => $recipe->kitchen_current_stock,
                            'quantity_change' => 0,
                            'quantity_after' => $recipe->kitchen_current_stock,
                            'description' => "INSUFFICIENT STOCK: {$saleDetail->menu_name} x{$saleDetail->quantity} (Sale #{$saleID}) - Required: {$totalRequired}, Available: {$recipe->kitchen_current_stock}",
                            'user_id' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            \Log::info("Completed kitchen stock deduction for sale #{$saleID}");

        } catch (\Exception $e) {
            \Log::error("Error processing kitchen stock deduction: " . $e->getMessage(), [
                'sale_id' => $saleID,
                'error' => $e->getTraceAsString()
            ]);
            // Don't throw the error as this shouldn't stop the sale process
        }
    }
}