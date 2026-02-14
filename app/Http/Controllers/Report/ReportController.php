<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Category;

use App\Exports\SaleReportExport;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    //
    public function index(){
        return view('report.index');
    }
    public function show(Request $request){
        $request->validate([
            'dateStart' => 'required',
            'dateEnd' => 'required'
        ]);
        $dateStart = date("Y-m-d H:i:s", strtotime($request->dateStart.' 00:00:00'));
        $dateEnd = date("Y-m-d H:i:s", strtotime($request->dateEnd.' 23:59:59'));

        $sales = Sale::whereBetween('updated_at', [$dateStart, $dateEnd])->whereIn('sale_status', ['paid', 'cancelled']);
      
        $summarySales = Sale::select('menu_id','menu_name','categories.name', DB::raw('SUM(sale_details.quantity) as qty_sum'))
        ->join('sale_details', 'sales.id', '=', 'sale_details.sale_id')
        ->join('menus', 'menus.id', '=', 'sale_details.menu_id')
        ->join('categories', 'categories.id', '=', 'menus.category_id')
        
        ->whereBetween('sales.updated_at', [$dateStart, $dateEnd])
        ->where('sales.sale_status','paid')
        ->where('sale_details.quantity','>','0')

        ->groupBy('sale_details.menu_id','menu_name','categories.name')
        ->orderby('categories.name','asc')
        ->get();


        return view('report.showReport')->with('dateStart', date("m/d/Y H:i:s", strtotime($request->dateStart.' 00:00:00')))
        ->with('dateEnd', date("m/d/Y H:i:s", strtotime($request->dateEnd.' 23:59:59')))
        ->with('totalSale', $sales->sum('change'))
        ->with('serviceCharge', $sales->sum('total_recieved'))
        ->with('summarySales', $summarySales)
        ->with('sales', $sales->paginate(500));

    }
    public function export(Request $request){
        return Excel::download(new SaleReportExport($request->dateStart, $request->dateEnd), 'saleReport.xlsx');
    }

    /**
     * Admin-only: Cancel entire bill and restore stock
     */
    public function cancelBill(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized. Admin access required.'], 403);
        }

        $request->validate([
            'sale_id' => 'required|integer',
            'reason' => 'required|string|max:500'
        ]);

        $sale = Sale::find($request->sale_id);
        if (!$sale) {
            return response()->json(['error' => 'Sale not found.'], 404);
        }

        if ($sale->sale_status === 'cancelled') {
            return response()->json(['error' => 'This bill is already cancelled.'], 400);
        }

        DB::beginTransaction();
        try {
            $saleDetails = SaleDetail::where('sale_id', $sale->id)->get();
            $restoredItems = [];

            // Restore stock for each sale detail
            foreach ($saleDetails as $saleDetail) {
                $restored = $this->restoreStockForSaleDetail($saleDetail, $sale->id);
                if (!empty($restored)) {
                    $restoredItems = array_merge($restoredItems, $restored);
                }
            }

            // Mark sale as cancelled
            $oldStatus = $sale->sale_status;
            $oldTotal = $sale->total_price;
            $sale->sale_status = 'cancelled';
            $sale->save();

            // Log the action
            $itemNames = $saleDetails->pluck('menu_name')->implode(', ');
            $stockInfo = !empty($restoredItems) ? ' | Stock restored: ' . implode(', ', $restoredItems) : ' | No stock to restore';
            DB::table('menu_activity_logs')->insert([
                'action' => 'bill_cancelled',
                'menu_id' => null,
                'menu_name' => null,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'details' => "Cancelled Bill #{$sale->id} (Table: {$sale->table_name}, Amount: Rs " . number_format($oldTotal, 2) . ", Items: {$itemNames}). Reason: {$request->reason}{$stockInfo}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Bill #{$sale->id} cancelled successfully. " . count($restoredItems) . " stock item(s) restored.",
                'restored_items' => $restoredItems
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error cancelling bill: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to cancel bill. ' . $e->getMessage()], 500);
        }
    }

    /**
     * Admin-only: Void a single item from a bill and restore its stock
     */
    public function voidItem(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized. Admin access required.'], 403);
        }

        $request->validate([
            'sale_detail_id' => 'required|integer',
            'reason' => 'required|string|max:500'
        ]);

        $saleDetail = SaleDetail::find($request->sale_detail_id);
        if (!$saleDetail) {
            return response()->json(['error' => 'Sale item not found.'], 404);
        }

        $sale = Sale::find($saleDetail->sale_id);
        if (!$sale) {
            return response()->json(['error' => 'Sale not found.'], 404);
        }

        if ($sale->sale_status === 'cancelled') {
            return response()->json(['error' => 'This bill is already cancelled.'], 400);
        }

        DB::beginTransaction();
        try {
            $itemName = $saleDetail->menu_name;
            $itemQty = $saleDetail->quantity;
            $itemPrice = $saleDetail->menu_price;
            $itemTotal = $itemPrice * $itemQty;

            // Restore stock for this item
            $restoredItems = $this->restoreStockForSaleDetail($saleDetail, $sale->id);

            // Delete the sale detail
            $saleDetail->delete();

            // Recalculate sale total
            $remainingTotal = SaleDetail::where('sale_id', $sale->id)->sum(DB::raw('menu_price * quantity'));
            $sale->total_price = $remainingTotal;

            // Check if any items remain
            $remainingCount = SaleDetail::where('sale_id', $sale->id)->count();
            if ($remainingCount === 0) {
                $sale->sale_status = 'cancelled';
            }
            $sale->save();

            // Log the action
            $stockInfo = !empty($restoredItems) ? ' | Stock restored: ' . implode(', ', $restoredItems) : '';
            DB::table('menu_activity_logs')->insert([
                'action' => 'item_voided',
                'menu_id' => null,
                'menu_name' => $itemName,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'details' => "Voided item: {$itemName} x{$itemQty} (Rs " . number_format($itemTotal, 2) . ") from Bill #{$sale->id}. Reason: {$request->reason}{$stockInfo}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "{$itemName} x{$itemQty} voided from Bill #{$sale->id}. " . count($restoredItems) . " stock item(s) restored.",
                'restored_items' => $restoredItems,
                'new_total' => $remainingTotal,
                'remaining_items' => $remainingCount,
                'sale_cancelled' => $remainingCount === 0
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error voiding item: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to void item. ' . $e->getMessage()], 500);
        }
    }

    /**
     * Restore kitchen stock for a sale detail item based on its recipe
     */
    private function restoreStockForSaleDetail($saleDetail, $saleId)
    {
        $restoredItems = [];

        // Get recipes for this menu item
        $recipes = DB::table('menu_item_recipes')
            ->join('items', 'menu_item_recipes.item_id', '=', 'items.id')
            ->where('menu_item_recipes.menu_id', $saleDetail->menu_id)
            ->where('items.is_kitchen_item', true)
            ->where('items.kitchen_is_active', true)
            ->select(
                'menu_item_recipes.*',
                'items.name as item_name',
                'items.kitchen_current_stock',
                'items.kitchen_unit'
            )
            ->get();

        foreach ($recipes as $recipe) {
            $totalToRestore = $recipe->required_quantity * $saleDetail->quantity;
            $oldStock = $recipe->kitchen_current_stock;
            $newStock = $oldStock + $totalToRestore;

            // Restore kitchen stock
            DB::table('items')
                ->where('id', $recipe->item_id)
                ->update([
                    'kitchen_current_stock' => $newStock,
                    'updated_at' => now()
                ]);

            // Log the restoration in kitchen_stock_logs
            DB::table('kitchen_stock_logs')->insert([
                'item_id' => $recipe->item_id,
                'action' => 'stock_restored',
                'quantity_before' => $oldStock,
                'quantity_change' => $totalToRestore,
                'quantity_after' => $newStock,
                'description' => "RESTORED: {$saleDetail->menu_name} x{$saleDetail->quantity} (Bill #{$saleId} cancelled/voided)",
                'user_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $restoredItems[] = "{$recipe->item_name} +{$totalToRestore} {$recipe->kitchen_unit}";
        }

        return $restoredItems;
    }
}
