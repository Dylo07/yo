<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\KitchenDailyStock;
use App\Models\StockLog;
use App\Models\Sale;
use App\Models\MenuItemRecipe;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KitchenDailyStockController extends Controller
{
    /**
     * Main daily stock sheet page
     */
    public function index(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $dateCarbon = Carbon::parse($date);

        // Get all tracked items
        $trackedItems = Item::where('is_kitchen_tracked', true)
            ->orderBy('name')
            ->get();

        // Get previous day
        $previousDate = $dateCarbon->copy()->subDay()->format('Y-m-d');

        // Get previous day's records keyed by item_id
        $previousRecords = KitchenDailyStock::where('date', $previousDate)
            ->get()
            ->keyBy('item_id');

        // Get today's saved records keyed by item_id
        $savedRecords = KitchenDailyStock::where('date', $date)
            ->get()
            ->keyBy('item_id');

        // Calculate data for each tracked item
        $items = [];
        foreach ($trackedItems as $item) {
            // Opening balance: from previous day record
            $openingBalance = 0;
            if (isset($previousRecords[$item->id])) {
                $prevRecord = $previousRecords[$item->id];
                if ($prevRecord->physical_count !== null) {
                    $openingBalance = (float) $prevRecord->physical_count;
                } else {
                    $openingBalance = (float) $prevRecord->expected_balance;
                }
            }

            // Received: SUM of stock_logs where action LIKE 'remove_main_kitchen%' for this date
            $received = (float) StockLog::where('item_id', $item->id)
                ->where('action', 'like', 'remove_main_kitchen%')
                ->whereDate('created_at', $date)
                ->sum('quantity');

            // Used: calculate from sales data
            $used = $this->calculateUsedFromSales($item->id, $date);

            // Expected balance
            $expectedBalance = $openingBalance + $received - $used;

            // Physical count from saved record
            $physicalCount = null;
            $notes = '';
            if (isset($savedRecords[$item->id])) {
                $physicalCount = $savedRecords[$item->id]->physical_count;
                $notes = $savedRecords[$item->id]->notes ?? '';
            }

            // Variance
            $variance = null;
            if ($physicalCount !== null) {
                $variance = (float) $physicalCount - $expectedBalance;
            }

            $items[] = [
                'item_id' => $item->id,
                'name' => $item->name,
                'unit' => $item->kitchen_unit,
                'opening_balance' => round($openingBalance, 3),
                'received' => round($received, 3),
                'used' => round($used, 3),
                'expected_balance' => round($expectedBalance, 3),
                'physical_count' => $physicalCount,
                'variance' => $variance !== null ? round($variance, 3) : null,
                'notes' => $notes,
            ];
        }

        return view('kitchen.daily_stock', compact('date', 'items', 'savedRecords'));
    }

    /**
     * Calculate ingredient usage from sales for a given item and date
     */
    private function calculateUsedFromSales($itemId, $date)
    {
        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay = Carbon::parse($date)->endOfDay();

        // Get non-cancelled sales for the date
        $sales = Sale::whereBetween('updated_at', [$startOfDay, $endOfDay])
            ->where('sale_status', '!=', 'cancelled')
            ->with('saleDetails')
            ->get();

        if ($sales->isEmpty()) {
            return 0;
        }

        $totalUsed = 0;

        foreach ($sales as $sale) {
            if (!$sale->saleDetails || $sale->saleDetails->isEmpty()) {
                continue;
            }

            foreach ($sale->saleDetails as $detail) {
                if ($detail->quantity <= 0) {
                    continue;
                }

                // Find recipes for this menu item that use the given ingredient
                $recipes = MenuItemRecipe::where('menu_id', $detail->menu_id)
                    ->where('item_id', $itemId)
                    ->get();

                foreach ($recipes as $recipe) {
                    $totalUsed += (float) $recipe->required_quantity * $detail->quantity;
                }
            }
        }

        return $totalUsed;
    }

    /**
     * Save daily stock data via AJAX
     */
    public function save(Request $request)
    {
        $date = $request->input('date');
        $itemsData = $request->input('items', []);

        if (!$date || empty($itemsData)) {
            return response()->json(['success' => false, 'message' => 'No data to save.'], 400);
        }

        $dateCarbon = Carbon::parse($date);
        $previousDate = $dateCarbon->copy()->subDay()->format('Y-m-d');

        // Get previous day records
        $previousRecords = KitchenDailyStock::where('date', $previousDate)
            ->get()
            ->keyBy('item_id');

        $userName = auth()->user() ? auth()->user()->name : null;
        $userId = auth()->id();

        foreach ($itemsData as $data) {
            $itemId = $data['item_id'];
            $physicalCount = isset($data['physical_count']) && $data['physical_count'] !== '' ? (float) $data['physical_count'] : null;
            $notes = $data['notes'] ?? null;

            // Recalculate values
            $openingBalance = 0;
            if (isset($previousRecords[$itemId])) {
                $prevRecord = $previousRecords[$itemId];
                if ($prevRecord->physical_count !== null) {
                    $openingBalance = (float) $prevRecord->physical_count;
                } else {
                    $openingBalance = (float) $prevRecord->expected_balance;
                }
            }

            $received = (float) StockLog::where('item_id', $itemId)
                ->where('action', 'like', 'remove_main_kitchen%')
                ->whereDate('created_at', $date)
                ->sum('quantity');

            $used = $this->calculateUsedFromSales($itemId, $date);
            $expectedBalance = $openingBalance + $received - $used;

            $variance = null;
            if ($physicalCount !== null) {
                $variance = $physicalCount - $expectedBalance;
            }

            KitchenDailyStock::updateOrCreate(
                ['date' => $date, 'item_id' => $itemId],
                [
                    'opening_balance' => round($openingBalance, 3),
                    'received' => round($received, 3),
                    'used' => round($used, 3),
                    'expected_balance' => round($expectedBalance, 3),
                    'physical_count' => $physicalCount !== null ? round($physicalCount, 3) : null,
                    'variance' => $variance !== null ? round($variance, 3) : null,
                    'notes' => $notes,
                    'entered_by' => $userName,
                    'user_id' => $userId,
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Daily stock sheet saved successfully.']);
    }

    /**
     * Settings page to select tracked items
     */
    public function settings()
    {
        $items = Item::where('is_kitchen_item', true)
            ->orderBy('name')
            ->get()
            ->groupBy(function ($item) {
                return $item->group ? $item->group->name : 'Ungrouped';
            });

        // Get currently tracked item IDs
        $trackedIds = Item::where('is_kitchen_tracked', true)->pluck('id')->toArray();

        return view('kitchen.daily_stock_settings', compact('items', 'trackedIds'));
    }

    /**
     * Save tracked items settings
     */
    public function updateSettings(Request $request)
    {
        $trackedItemIds = $request->input('tracked_items', []);

        // Reset all items
        Item::where('is_kitchen_tracked', true)->update(['is_kitchen_tracked' => false]);

        // Set selected items as tracked
        if (!empty($trackedItemIds)) {
            Item::whereIn('id', $trackedItemIds)->update(['is_kitchen_tracked' => true]);
        }

        return redirect()->route('kitchen.daily-stock.settings')
            ->with('success', 'Kitchen daily stock tracking settings updated successfully.');
    }

    /**
     * Carry forward balances to next day
     */
    public function carryForward(Request $request)
    {
        $date = $request->input('date');

        if (!$date) {
            return response()->json(['success' => false, 'message' => 'Date is required.'], 400);
        }

        $currentDate = Carbon::parse($date);
        $nextDate = $currentDate->copy()->addDay()->format('Y-m-d');

        // Get current day records
        $currentRecords = KitchenDailyStock::where('date', $date)->get();

        if ($currentRecords->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No records found for this date. Save the current day first.'], 400);
        }

        foreach ($currentRecords as $record) {
            $carryValue = $record->physical_count !== null
                ? (float) $record->physical_count
                : (float) $record->expected_balance;

            KitchenDailyStock::updateOrCreate(
                ['date' => $nextDate, 'item_id' => $record->item_id],
                [
                    'opening_balance' => round($carryValue, 3),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Balances carried forward to ' . $nextDate . ' successfully.',
            'next_date' => $nextDate,
        ]);
    }
}
