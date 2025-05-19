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
class CashierController extends Controller
{
    public function index() {
        $categories = Category::all();
        return view('cashier.index')->with('categories', $categories);
    }

    public function getTables(){
        $tables = Table::all();
        $html = '';
        
        foreach($tables as $table){
            $html .= '<div class="col-lg-2 col-md-3 col-sm-1 mb-2">';
            $html .= '<button tabindex ="-1" class="btn btn-dark btn-outline-secondary btn-table" data-id="'.$table->id.'" data-name="'.$table->name.'" >
            <img class="img-fluid" style="width:0%" src="'.url('/image/table.svg').'"/>
            <br>';
            if($table->status == "available"){
                $html .= '<span class="badge badge-pill badge-success">'.$table->name.'</span>';
            }else{
                $html .= '<span class="badge badge-pill badge-danger">'.$table->name.'</span>';
            }
            $html .='</button>';
            $html .= '</div>';
        }
        return $html;
    }
    
    public function getMenuByCategory($category_id,$search_keyword = ''){
        if($category_id == 0){
            $menus = Menu::where('name', 'LIKE', "%{$search_keyword}%")->get();
        }else{
            $menus = Menu::where('category_id', $category_id)->get();
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
        $sale->save();
        $sale_id = $sale->id;
        
        $table = Table::find($table_id);
        $table->status = "unavailable";
        $table->save();
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

    $sale->total_price = $sale->total_price + ($request->quantity * $menu->price);
    $sale->save();

    return [
        'html' => $this->getSaleDetails($sale_id),
        'tableStatusChanged' => $tableStatusChanged,
        'tableId' => $table_id
    ];
}
    public function getSaleDetailsByTable($table_id){
        $sale = Sale::where('table_id', $table_id)->where('sale_status','unpaid')->first();
        $html = '';
        if($sale){
            $sale_id = $sale->id;
            $html .= $this->getSaleDetails($sale_id);
        }else{
            $html .= "Not Found Any Sale Details for the Selected Table";
        }
        return $html;
    }

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
    $hasAdvancePayment = false; // Flag to check if there's already an advance payment
    $hasRegularMenuItems = false; // Flag to check if there are regular menu items
    
    foreach($saleDetails as $saleDetail){
        // Check if this is an advance payment item
        if (strpos($saleDetail->menu_name, 'Advance Payment') !== false) {
            $hasAdvancePayment = true;
        } else {
            $hasRegularMenuItems = true;
        }
        
        $updatedDateTime = $saleDetail->updated_at ? $saleDetail->updated_at->format('d/m/Y H:i:s') : '';
        $html .= '
        <tr>
            <td>'.$saleDetail->menu_name.'</td>
            <td><input type="number" tabindex ="-1" class="change-quantity" data-id="'.$saleDetail->id.'" 
                       style="width:50px;" value="'.$saleDetail->quantity.'"'.
                       ($saleDetail->status == "confirm" ? ' disabled' : '').'></td>
            <td>'.$saleDetail->menu_price.'</td>
            <td>'.($saleDetail->menu_price * $saleDetail->quantity).'</td>
            <td>'.$updatedDateTime.'</td>';
            if($saleDetail->status == "noConfirm"){
                $showBtnPayment = false;
                $html .= '<td><a data-id="'.$saleDetail->id.'" class="btn btn-danger btn-delete-saledetail"><i class="far fa-trash-alt"></a></td>';
            }else{
                $html .= '<td><i class="fas fa-check-circle"></i></td>';
            }
        $html .= '</tr>';
    }
    $html .='</tbody></table></div>';

    $sale = Sale::find($sale_id);
    $html .= '<hr>';
    $html .= '<h3>Total Amount: Rs '.number_format($sale->total_price).'</h3>';

    if($showBtnPayment){
        $html .= '<button data-id="'.$sale_id.'" data-totalAmount="'.$sale->total_price.'" class="btn btn-success btn-block btn-payment" data-toggle="modal" data-target="#exampleModal">Payment</button>';
        $html .= '<button data-id="'.$sale_id.'" class="btn btn-dark btn-block btn-payment printKot">Print KOT</button>';
        
        // Only show advance payment button if there are no regular menu items AND no existing advance payment
        if (!$hasRegularMenuItems && !$hasAdvancePayment) {
            $html .= '<a href="'.url('/cashier/advance-payment/'.$sale_id).'" class="btn btn-primary btn-block">Advance Payment</a>';
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
        
        $sale = Sale::where('id', $saleDetail->sale_id)->first();
        $sale->total_price = $sale->total_price + $saleDetail->menu_price;
        $sale->save();
        
        return $this->getSaleDetails($saleDetail->sale_id);
    }

    public function changesQuantity(Request $request){
        $saleDetail_id = $request->saleDetail_id;
        $qty = $request->qty;
        $saleDetail = SaleDetail::where('id',$saleDetail_id)->first();
      
        $sale = Sale::where('id', $saleDetail->sale_id)->first();
        $Removetotal_price = $saleDetail->quantity * $saleDetail->menu_price;

        $saleDetail->quantity = $qty;
        $saleDetail->save();
        
        $remaing = $sale->total_price - $Removetotal_price;
        $newTot = $remaing + ($qty * $saleDetail->menu_price);
        $sale->total_price = $newTot;
        $sale->save();
        
        return $this->getSaleDetails($saleDetail->sale_id);
    }

    public function decreaseQuantity(Request $request){
        $saleDetail_id = $request->saleDetail_id;
        $saleDetail = SaleDetail::where('id',$saleDetail_id)->first();
        $saleDetail->quantity = $saleDetail->quantity - 1;
        $saleDetail->save();
        
        $sale = Sale::where('id', $saleDetail->sale_id)->first();
        $sale->total_price = $sale->total_price - abs($saleDetail->menu_price);
        $sale->save();
        
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

    public function deleteSaleDetail(Request $request){
        $saleDetail_id = $request->saleDetail_id;
        $saleDetail = SaleDetail::find($saleDetail_id);
        $sale_id = $saleDetail->sale_id;
        $menu_price = ($saleDetail->menu_price * $saleDetail->quantity);
        $saleDetail->delete();

        $sale = Sale::find($sale_id);
        $sale->total_price = $sale->total_price - $menu_price;
        $sale->save();
        
        $saleDetails = SaleDetail::where('sale_id', $sale_id)->first();
        if($saleDetail){
            $html = $this->getSaleDetails($sale_id);
        }else{
            $html = "Not Found Any Sale Details for the Selected Table";
        }
        return $html;
    }
public function savePayment(Request $request){
    $saleID = $request->saleID;
    $recievedAmount = $request->recievedAmount;
    $paymentType = $request->PaymentType;
    
    // Begin transaction for data consistency
    DB::beginTransaction();
    
    try {
        $sale = Sale::find($saleID);
        $sale->total_recieved = $recievedAmount;
        $sale->change = $recievedAmount + $sale->total_price;
        $sale->payment_type = $paymentType;
        $sale->sale_status = "paid";
        $sale->save();
        
        $table = Table::find($sale->table_id);
        $table->status = "available";
        $table->save();
        
        // Only reduce stock if it hasn't been reduced yet
        if (!$this->hasStockBeenReduced($saleID)) {
            $saleDetail = SaleDetail::where('sale_id', $saleID)->get();
            
            foreach ($saleDetail as $value) {
                $user = Auth::user();
                $stock = new InStock();
                $stock->menu_id = $value->menu_id;
                $stock->stock = -intval($value->quantity);
                $stock->user_id = $user->id;
                $stock->sale_id = $saleID; // Track which sale this stock reduction belongs to
                $stock->save();
    
                $menu = Menu::find($value->menu_id);
                $menu->stock = intval($menu->stock) - ($value->quantity);
                $menu->save();     
            }
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
    // Add this method to your CashierController.php file

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
    
    // Redirect to the advance payment selection
    return redirect('/cashier/advance-payment/' . $sale->id);
}
}