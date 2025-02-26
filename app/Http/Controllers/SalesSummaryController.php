<?php

namespace App\Http\Controllers;

use App\Models\SalesSummary;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Log;

class SalesSummaryController extends Controller
{
    public function index()
    {
        return view('sales.summary.index');
    }

    public function getSummaryData(Request $request)
    {
        try {
            $viewType = $request->input('viewType', 'daily');
            $date = $request->input('date', Carbon::now()->format('Y-m-d'));
            
            $carbon = Carbon::parse($date);
            
            // Get the data based on view type
            $data = DB::table('sales')
                ->join('sale_details', 'sales.id', '=', 'sale_details.sale_id')
                ->join('menus', 'sale_details.menu_id', '=', 'menus.id')
                ->join('categories', 'menus.category_id', '=', 'categories.id')
                ->select(
                    'sale_details.menu_id',
                    'menus.name as menu_name',
                    'categories.name as category_name',
                    DB::raw('SUM(sale_details.quantity) as total_quantity'),
                    DB::raw('SUM(sale_details.quantity * sale_details.menu_price) as total_revenue'),
                    DB::raw('AVG(sale_details.menu_price) as average_price')
                )
                ->where('sales.sale_status', 'paid');

            // Add date conditions based on view type
            switch ($viewType) {
                case 'daily':
                    $data->whereDate('sales.created_at', $date);
                    break;
                case 'monthly':
                    $data->whereYear('sales.created_at', $carbon->year)
                         ->whereMonth('sales.created_at', $carbon->month);
                    break;
                case 'yearly':
                    $data->whereYear('sales.created_at', $carbon->year);
                    break;
            }

            // Group by necessary fields
            $data->groupBy('sale_details.menu_id', 'menus.name', 'categories.name');

            // Execute query and get results
            $summaryData = $data->get();

            // Calculate statistics
            $stats = $this->calculateStats($summaryData);

            return response()->json([
                'summary' => $summaryData,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getSummaryData: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error loading data: ' . $e->getMessage()], 500);
        }
    }

    private function calculateStats($summaryData)
    {
        try {
            // Category distribution
            $categoryDistribution = $summaryData->groupBy('category_name')
                ->map(function($items) {
                    return [
                        'quantity' => $items->sum('total_quantity'),
                        'revenue' => $items->sum('total_revenue'),
                        'average_price' => $items->avg('average_price')
                    ];
                });

            // Calculate overall statistics
            $totalRevenue = $summaryData->sum('total_revenue');
            $totalItems = $summaryData->sum('total_quantity');

            return [
                'total_items_sold' => $totalItems,
                'total_revenue' => $totalRevenue,
                'average_sale' => $totalItems > 0 ? $totalRevenue / $totalItems : 0,
                'categories_count' => $categoryDistribution->count(),
                'category_distribution' => $categoryDistribution,
                'trend_data' => [
                    'labels' => $summaryData->pluck('menu_name'),
                    'values' => $summaryData->pluck('total_revenue')
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating stats: ' . $e->getMessage());
            return [
                'total_items_sold' => 0,
                'total_revenue' => 0,
                'average_sale' => 0,
                'categories_count' => 0,
                'category_distribution' => [],
                'trend_data' => ['labels' => [], 'values' => []]
            ];
        }
    }

    public function printSummary(Request $request)
    {
        try {
            $data = $this->getSummaryData($request)->getData();
            return view('sales.summary.print', [
                'summaryData' => $data->summary,
                'stats' => $data->stats,
                'date' => $request->input('date', now()->format('Y-m-d')),
                'viewType' => $request->input('viewType', 'daily')
            ]);
        } catch (\Exception $e) {
            Log::error('Error in printSummary: ' . $e->getMessage());
            return back()->with('error', 'Error generating print summary');
        }
    }
}