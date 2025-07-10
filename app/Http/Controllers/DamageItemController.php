<?php

namespace App\Http\Controllers;

use App\Models\DamageItem;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DamageItemController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        
        // Parse the month to get year and month separately
        $carbonDate = Carbon::parse($month . '-01');
        $year = $carbonDate->year;
        $monthNumber = $carbonDate->month;
        
        // Query items for the specific month and year
        $items = DamageItem::whereYear('reported_date', $year)
            ->whereMonth('reported_date', $monthNumber)
            ->orderBy('reported_date', 'desc')
            ->get();
            
        $totalCost = $items->sum('total_cost');
        
        // Get yearly data for chart
        $yearlyData = $this->getYearlyData($year);
        
        return view('damage-items.index', compact('items', 'totalCost', 'month', 'yearlyData'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'type' => 'required|in:damaged,missing',
            'notes' => 'nullable|string',
            'reported_date' => 'required|date'
        ]);

        // Calculate total cost
        $validated['total_cost'] = $validated['quantity'] * $validated['unit_price'];

        DamageItem::create($validated);

        return redirect()->route('damage-items.index', ['month' => Carbon::parse($validated['reported_date'])->format('Y-m')])
            ->with('success', 'Item recorded successfully');
    }

    /**
     * Get yearly data for chart display
     */
    private function getYearlyData($year)
    {
        $monthlyData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $items = DamageItem::whereYear('reported_date', $year)
                ->whereMonth('reported_date', $month)
                ->get();
                
            $damagedCost = $items->where('type', 'damaged')->sum('total_cost');
            $missingCost = $items->where('type', 'missing')->sum('total_cost');
            
            $monthlyData[] = [
                'month' => Carbon::create($year, $month, 1)->format('M'),
                'damaged_cost' => $damagedCost,
                'missing_cost' => $missingCost,
                'total_cost' => $damagedCost + $missingCost,
                'damaged_count' => $items->where('type', 'damaged')->count(),
                'missing_count' => $items->where('type', 'missing')->count(),
                'total_count' => $items->count()
            ];
        }
        
        return $monthlyData;
    }

    /**
     * Get monthly report data (API endpoint)
     */
    public function monthlyReport(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $carbonDate = Carbon::parse($month . '-01');
        
        $items = DamageItem::whereYear('reported_date', $carbonDate->year)
            ->whereMonth('reported_date', $carbonDate->month)
            ->orderBy('reported_date', 'desc')
            ->get();
            
        $stats = [
            'total_items' => $items->count(),
            'damaged_items' => $items->where('type', 'damaged')->count(),
            'missing_items' => $items->where('type', 'missing')->count(),
            'total_cost' => $items->sum('total_cost'),
            'damaged_cost' => $items->where('type', 'damaged')->sum('total_cost'),
            'missing_cost' => $items->where('type', 'missing')->sum('total_cost')
        ];
        
        return response()->json([
            'items' => $items,
            'stats' => $stats,
            'month' => $carbonDate->format('F Y')
        ]);
    }

    /**
     * Get yearly chart data (API endpoint)
     */
    public function getYearlyChartData(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);
        $yearlyData = $this->getYearlyData($year);
        
        return response()->json($yearlyData);
    }

    /**
     * Export damage items report
     */
    public function exportReport(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $carbonDate = Carbon::parse($month . '-01');
        
        $items = DamageItem::whereYear('reported_date', $carbonDate->year)
            ->whereMonth('reported_date', $carbonDate->month)
            ->orderBy('reported_date', 'desc')
            ->get();
            
        // Generate CSV or PDF report here
        // For now, return the data
        return response()->json($items);
    }

    /**
     * Dashboard data for damage items overview
     */
    public function getDashboardData()
    {
        $currentMonth = Carbon::now();
        $previousMonth = Carbon::now()->subMonth();
        
        // Current month data
        $currentMonthItems = DamageItem::whereYear('reported_date', $currentMonth->year)
            ->whereMonth('reported_date', $currentMonth->month)
            ->get();
            
        // Previous month data
        $previousMonthItems = DamageItem::whereYear('reported_date', $previousMonth->year)
            ->whereMonth('reported_date', $previousMonth->month)
            ->get();
            
        // Calculate trends
        $currentTotal = $currentMonthItems->sum('total_cost');
        $previousTotal = $previousMonthItems->sum('total_cost');
        $costTrend = $previousTotal > 0 ? (($currentTotal - $previousTotal) / $previousTotal) * 100 : 0;
        
        $currentCount = $currentMonthItems->count();
        $previousCount = $previousMonthItems->count();
        $countTrend = $previousCount > 0 ? (($currentCount - $previousCount) / $previousCount) * 100 : 0;
        
        return response()->json([
            'current_month' => [
                'total_cost' => $currentTotal,
                'total_items' => $currentCount,
                'damaged_items' => $currentMonthItems->where('type', 'damaged')->count(),
                'missing_items' => $currentMonthItems->where('type', 'missing')->count()
            ],
            'trends' => [
                'cost_trend' => round($costTrend, 2),
                'count_trend' => round($countTrend, 2)
            ],
            'recent_items' => $currentMonthItems->take(5)
        ]);
    }
}