<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InStock;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WaterBottleController extends Controller
{
    // Water bottle menu ID and category ID
    const WATER_BOTTLE_MENU_ID = 2817;
    const WATER_BOTTLE_CATEGORY_ID = 4;

    /**
     * Display the water bottle room issuance page.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $waterBottle = Menu::find(self::WATER_BOTTLE_MENU_ID);
        
        if (!$waterBottle) {
            return redirect('/home')->with('error', 'Water bottle item not found in inventory.');
        }

        // Get date filter (default to today)
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));

        // Get ALL stock history for the selected date (both additions and reductions)
        $stockHistory = InStock::where('menu_id', self::WATER_BOTTLE_MENU_ID)
            ->whereDate('created_at', $date)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate totals for the day
        $totalIssuedToday = abs($stockHistory->where('stock', '<', 0)->sum('stock'));
        $totalAddedToday = $stockHistory->where('stock', '>', 0)->sum('stock');

        // Get monthly summary for current month
        $currentMonth = Carbon::parse($date);
        $monthStart = $currentMonth->copy()->startOfMonth();
        $monthEnd = $currentMonth->copy()->endOfMonth();

        $monthlyData = InStock::where('menu_id', self::WATER_BOTTLE_MENU_ID)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->get();

        $monthlyIssued = abs($monthlyData->where('stock', '<', 0)->sum('stock'));
        $monthlyAdded = $monthlyData->where('stock', '>', 0)->sum('stock');

        return view('water-bottle.index', [
            'waterBottle' => $waterBottle,
            'stockHistory' => $stockHistory,
            'totalIssuedToday' => $totalIssuedToday,
            'totalAddedToday' => $totalAddedToday,
            'monthlyIssued' => $monthlyIssued,
            'monthlyAdded' => $monthlyAdded,
            'selectedDate' => $date,
            'currentMonth' => $currentMonth->format('F Y')
        ]);
    }

    /**
     * Issue water bottles to a room.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function issue(Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'room_numbers' => 'required|string|max:255',
        ]);

        $waterBottle = Menu::find(self::WATER_BOTTLE_MENU_ID);

        if (!$waterBottle) {
            return redirect()->back()->with('error', 'Water bottle item not found.');
        }

        $quantity = intval($request->quantity);

        // Check if enough stock is available
        if ($waterBottle->stock < $quantity) {
            return redirect()->back()->with('error', 'Insufficient stock. Available: ' . $waterBottle->stock);
        }

        $user = Auth::user();

        // Create stock reduction entry with room number notes
        $stock = new InStock();
        $stock->menu_id = self::WATER_BOTTLE_MENU_ID;
        $stock->stock = -$quantity; // Negative for reduction
        $stock->user_id = $user->id;
        $stock->notes = 'Room: ' . $request->room_numbers;
        $stock->save();

        // Update menu stock
        $waterBottle->stock = intval($waterBottle->stock) - $quantity;
        $waterBottle->save();

        return redirect()->back()->with('success', $quantity . ' water bottle(s) issued to Room ' . $request->room_numbers);
    }

    /**
     * Get issuance report for a date range.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function report(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->subDays(7)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));

        $waterBottle = Menu::find(self::WATER_BOTTLE_MENU_ID);

        $issuanceHistory = InStock::where('menu_id', self::WATER_BOTTLE_MENU_ID)
            ->where('stock', '<', 0)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by date
        $groupedByDate = $issuanceHistory->groupBy(function($item) {
            return Carbon::parse($item->created_at)->format('Y-m-d');
        });

        $totalIssued = abs($issuanceHistory->sum('stock'));

        return view('water-bottle.report', [
            'waterBottle' => $waterBottle,
            'groupedByDate' => $groupedByDate,
            'totalIssued' => $totalIssued,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
}
