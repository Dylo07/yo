<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StockLog;
use App\Models\Sale;
use App\Models\SaleDetail;
use Carbon\Carbon;

class KitchenComparisonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Get date range from request or use today as default
        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        
        // Validate dates
        if (!$startDate || !$endDate) {
            $startDate = now()->toDateString();
            $endDate = now()->toDateString();
        }
        
        // Ensure start date is not after end date
        if (Carbon::parse($startDate)->gt(Carbon::parse($endDate))) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }
        
        // Get daily sales data for the date range
        $dailySalesData = $this->getDailySalesData($startDate, $endDate);
        
        // Get main kitchen issued stock data for the date range
        $mainKitchenData = $this->getMainKitchenData($startDate, $endDate);
        
        // Create comparison data
        $comparisonData = $this->createComparisonData($dailySalesData, $mainKitchenData);
        
        return view('kitchen.comparison', compact(
            'startDate',
            'endDate', 
            'dailySalesData', 
            'mainKitchenData', 
            'comparisonData'
        ));
    }

    /**
     * ============================================
     * NEW METHOD - ONLY FOR PRINT VIEW
     * This is the ONLY addition to your controller
     * ============================================
     */
    public function print(Request $request)
    {
        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        
        if (!$startDate || !$endDate) {
            $startDate = now()->toDateString();
            $endDate = now()->toDateString();
        }
        
        if (Carbon::parse($startDate)->gt(Carbon::parse($endDate))) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }
        
        $dailySalesData = $this->getDailySalesData($startDate, $endDate);
        $mainKitchenData = $this->getMainKitchenData($startDate, $endDate);
        $comparisonData = $this->createComparisonData($dailySalesData, $mainKitchenData);
        
        // Only difference: returns 'comparison_print' view instead of 'comparison'
        return view('kitchen.comparison_print', compact(
            'startDate',
            'endDate', 
            'dailySalesData', 
            'mainKitchenData', 
            'comparisonData'
        ));
    }
    // ============================================
    // END OF NEW METHOD
    // Everything below is YOUR ORIGINAL CODE
    // ============================================

    private function getDailySalesData($startDate, $endDate)
    {
        try {
            // Convert dates to Carbon instances
            $startCarbon = Carbon::parse($startDate)->startOfDay();
            $endCarbon = Carbon::parse($endDate);
            
            // For today's end date, use current time; for past dates, use end of day
            if ($endCarbon->isToday()) {
                $endCarbon = now();
            } else {
                $endCarbon = $endCarbon->endOfDay();
            }
            
            \Log::info('Getting daily sales data for date range', [
                'start_date' => $startCarbon->format('Y-m-d H:i:s'),
                'end_date' => $endCarbon->format('Y-m-d H:i:s'),
                'is_single_day' => $startCarbon->isSameDay($endCarbon),
                'days_span' => $startCarbon->diffInDays($endCarbon) + 1
            ]);

            // Get all sales in the date range (any status for debugging)
            $allSales = Sale::whereBetween('updated_at', [$startCarbon, $endCarbon])->get();
            \Log::info('All sales found (any status)', [
                'count' => $allSales->count(),
                'statuses' => $allSales->pluck('sale_status')->unique()->toArray(),
                'date_range' => $allSales->pluck('updated_at')->map(function($date) {
                    return $date->format('Y-m-d H:i');
                })->unique()->sort()->values()->toArray()
            ]);

            // Try different status combinations
            $salesQuery = Sale::whereBetween('updated_at', [$startCarbon, $endCarbon]);
            
            // First try with 'paid' status
            $sales = (clone $salesQuery)->where('sale_status', 'paid')
                ->with(['saleDetails'])
                ->get();
            
            \Log::info('Paid sales found', [
                'count' => $sales->count()
            ]);

            // If no paid sales, try other statuses
            if ($sales->isEmpty()) {
                $sales = (clone $salesQuery)->whereIn('sale_status', ['active', 'pending', 'completed'])
                    ->with(['saleDetails'])
                    ->get();
                    
                \Log::info('Active/Pending/Completed sales found', [
                    'count' => $sales->count(),
                    'statuses' => $sales->pluck('sale_status')->unique()->toArray()
                ]);
            }

            // If still no sales, get ANY sales for debugging
            if ($sales->isEmpty()) {
                $sales = (clone $salesQuery)->with(['saleDetails'])->get();
                \Log::info('Using ANY status sales for debugging', [
                    'count' => $sales->count(),
                    'statuses' => $sales->pluck('sale_status')->unique()->toArray()
                ]);
            }
            
            if ($sales->isEmpty()) {
                \Log::warning('No sales found for date range', [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]);
                
                return [
                    'by_category' => [],
                    'total_items' => 0,
                    'total_sales' => 0,
                    'date_range' => "$startDate to $endDate"
                ];
            }

            $totalItems = 0;
            $totalSales = $sales->count();
            $categorizedData = [];

            // Get all unique menu IDs from sale details
            $allSaleDetails = $sales->flatMap(function($sale) {
                return $sale->saleDetails ?? collect();
            });
            
            if ($allSaleDetails->isEmpty()) {
                \Log::warning('No sale details found in sales');
                return [
                    'by_category' => [],
                    'total_items' => 0,
                    'total_sales' => $totalSales,
                    'date_range' => "$startDate to $endDate"
                ];
            }
            
            $menuIds = $allSaleDetails->pluck('menu_id')->unique()->filter()->toArray();
            
            \Log::info('Menu IDs to load', [
                'menu_ids' => $menuIds,
                'count' => count($menuIds)
            ]);
            
            // Load menus with categories
            $menus = \App\Models\Menu::whereIn('id', $menuIds)
                ->with('category')
                ->get()
                ->keyBy('id');
            
            \Log::info('Loaded menus', [
                'menu_count' => $menus->count()
            ]);

            // Process each sale
            foreach ($sales as $sale) {
                if (!$sale->saleDetails || $sale->saleDetails->isEmpty()) {
                    continue;
                }
                
                foreach ($sale->saleDetails as $detail) {
                    // Skip items with 0 or negative quantity
                    if ($detail->quantity <= 0) continue;
                    
                    // Get menu data
                    $menu = $menus->get($detail->menu_id);
                    
                    if (!$menu) {
                        // Use a default category for unknown menus
                        $categoryId = 'unknown';
                        $categoryName = 'Unknown Category';
                    } else {
                        $categoryId = $menu->category_id ?? 'uncategorized';
                        $categoryName = $menu->category ? $menu->category->name : 'Uncategorized';
                    }

                    if (!isset($categorizedData[$categoryId])) {
                        $categorizedData[$categoryId] = [
                            'name' => $categoryName,
                            'items' => [],
                            'total' => 0
                        ];
                    }

                    $menuName = $menu ? $menu->name : $detail->menu_name;
                    $itemKey = $detail->menu_id;

                    if (!isset($categorizedData[$categoryId]['items'][$itemKey])) {
                        $categorizedData[$categoryId]['items'][$itemKey] = [
                            'name' => $menuName,
                            'quantity' => 0
                        ];
                    }

                    $categorizedData[$categoryId]['items'][$itemKey]['quantity'] += $detail->quantity;
                    $categorizedData[$categoryId]['total'] += $detail->quantity;
                    $totalItems += $detail->quantity;
                }
            }

            // Convert items array to indexed array
            foreach ($categorizedData as &$category) {
                $category['items'] = array_values($category['items']);
            }

            return [
                'by_category' => $categorizedData,
                'total_items' => $totalItems,
                'total_sales' => $totalSales,
                'date_range' => "$startDate to $endDate"
            ];

        } catch (\Exception $e) {
            \Log::error('Error fetching daily sales data', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'by_category' => [],
                'total_items' => 0,
                'total_sales' => 0,
                'date_range' => "$startDate to $endDate"
            ];
        }
    }

    private function getMainKitchenData($startDate, $endDate)
    {
        try {
            // Convert dates to Carbon instances for proper querying
            $startCarbon = Carbon::parse($startDate)->startOfDay();
            $endCarbon = Carbon::parse($endDate)->endOfDay();
            
            \Log::info('Getting main kitchen data for date range', [
                'start_date' => $startCarbon->format('Y-m-d H:i:s'),
                'end_date' => $endCarbon->format('Y-m-d H:i:s')
            ]);

            // YOUR ORIGINAL QUERY - UNCHANGED
            $mainKitchenLogs = StockLog::with(['user', 'item', 'item.group'])
                ->whereBetween('created_at', [$startCarbon, $endCarbon])
                ->where('action', 'remove_main_kitchen')  // YOUR ORIGINAL ACTION
                ->orderBy('created_at', 'desc')
                ->get();

            \Log::info('Main kitchen logs found', [
                'count' => $mainKitchenLogs->count()
            ]);

            $categorizedData = [];
            $totalQuantity = 0;
            $totalTransactions = $mainKitchenLogs->count();

            foreach ($mainKitchenLogs as $log) {
                $categoryId = $log->item->group_id ?? 'uncategorized';
                $categoryName = $log->item->group->name ?? 'Uncategorized';

                if (!isset($categorizedData[$categoryId])) {
                    $categorizedData[$categoryId] = [
                        'name' => $categoryName,
                        'items' => [],
                        'total_quantity' => 0,
                        'total_transactions' => 0
                    ];
                }

                $categorizedData[$categoryId]['items'][] = [
                    'name' => $log->item->name,
                    'quantity' => $log->quantity,
                    'user' => $log->user->name,
                    'time' => $log->created_at->format('M d, H:i'),
                    'description' => $log->description
                ];

                $categorizedData[$categoryId]['total_quantity'] += $log->quantity;
                $categorizedData[$categoryId]['total_transactions']++;
                $totalQuantity += $log->quantity;
            }

            return [
                'by_category' => $categorizedData,
                'total_quantity' => $totalQuantity,
                'total_transactions' => $totalTransactions,
                'raw_logs' => $mainKitchenLogs,
                'date_range' => "$startDate to $endDate"
            ];

        } catch (\Exception $e) {
            \Log::error('Error fetching main kitchen data', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage()
            ]);

            return [
                'by_category' => [],
                'total_quantity' => 0,
                'total_transactions' => 0,
                'raw_logs' => collect(),
                'date_range' => "$startDate to $endDate"
            ];
        }
    }

    private function createComparisonData($dailySalesData, $mainKitchenData)
    {
        $comparison = [
            'matches' => [],
            'sales_only' => [],
            'kitchen_only' => [],
            'summary' => [
                'total_sales_items' => $dailySalesData['total_items'],
                'total_kitchen_quantity' => $mainKitchenData['total_quantity'],
                'categories_in_sales' => count($dailySalesData['by_category']),
                'categories_in_kitchen' => count($mainKitchenData['by_category']),
                'matching_categories' => 0,
                'total_sales_count' => $dailySalesData['total_sales'],
                'total_kitchen_transactions' => $mainKitchenData['total_transactions']
            ]
        ];

        $salesCategories = array_keys($dailySalesData['by_category']);
        $kitchenCategories = array_keys($mainKitchenData['by_category']);
        
        // Find matching categories
        $matchingCategories = array_intersect($salesCategories, $kitchenCategories);
        $comparison['summary']['matching_categories'] = count($matchingCategories);

        // Categories only in sales
        $salesOnlyCategories = array_diff($salesCategories, $kitchenCategories);
        foreach ($salesOnlyCategories as $categoryId) {
            $comparison['sales_only'][] = $dailySalesData['by_category'][$categoryId];
        }

        // Categories only in kitchen
        $kitchenOnlyCategories = array_diff($kitchenCategories, $salesCategories);
        foreach ($kitchenOnlyCategories as $categoryId) {
            $comparison['kitchen_only'][] = $mainKitchenData['by_category'][$categoryId];
        }

        // Matching categories with detailed comparison
        foreach ($matchingCategories as $categoryId) {
            $salesCategory = $dailySalesData['by_category'][$categoryId];
            $kitchenCategory = $mainKitchenData['by_category'][$categoryId];

            $comparison['matches'][] = [
                'category_name' => $salesCategory['name'],
                'sales_data' => $salesCategory,
                'kitchen_data' => $kitchenCategory,
                'sales_total' => $salesCategory['total'],
                'kitchen_total' => $kitchenCategory['total_quantity'],
                'difference' => $kitchenCategory['total_quantity'] - $salesCategory['total']
            ];
        }

        return $comparison;
    }

    public function exportComparison(Request $request)
    {
        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $format = $request->input('format', 'csv');
        
        // Get data
        $dailySalesData = $this->getDailySalesData($startDate, $endDate);
        $mainKitchenData = $this->getMainKitchenData($startDate, $endDate);
        $comparisonData = $this->createComparisonData($dailySalesData, $mainKitchenData);

        if ($format === 'csv') {
            $fileName = "kitchen_comparison_{$startDate}_to_{$endDate}.csv";
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$fileName\"",
            ];

            $callback = function () use ($comparisonData, $startDate, $endDate) {
                $file = fopen('php://output', 'w');
                
                // Add header information
                fputcsv($file, ['Kitchen vs Sales Comparison Report']);
                fputcsv($file, ['Date Range', "$startDate to $endDate"]);
                fputcsv($file, ['Generated', now()->format('Y-m-d H:i:s')]);
                fputcsv($file, []);
                
                // Summary section
                fputcsv($file, ['SUMMARY']);
                fputcsv($file, ['Total Sales Items', $comparisonData['summary']['total_sales_items']]);
                fputcsv($file, ['Total Sales Count', $comparisonData['summary']['total_sales_count']]);
                fputcsv($file, ['Total Kitchen Quantity', $comparisonData['summary']['total_kitchen_quantity']]);
                fputcsv($file, ['Total Kitchen Transactions', $comparisonData['summary']['total_kitchen_transactions']]);
                fputcsv($file, ['Categories in Sales', $comparisonData['summary']['categories_in_sales']]);
                fputcsv($file, ['Categories in Kitchen', $comparisonData['summary']['categories_in_kitchen']]);
                fputcsv($file, ['Matching Categories', $comparisonData['summary']['matching_categories']]);
                fputcsv($file, []);
                
                // Detailed comparison
                fputcsv($file, ['DETAILED COMPARISON']);
                fputcsv($file, ['Category', 'Sales Total', 'Kitchen Total', 'Difference', 'Status']);
                
                foreach ($comparisonData['matches'] as $match) {
                    fputcsv($file, [
                        $match['category_name'],
                        $match['sales_total'],
                        $match['kitchen_total'],
                        $match['difference'],
                        $match['difference'] >= 0 ? 'Kitchen >= Sales' : 'Kitchen < Sales'
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return response()->json(['error' => 'Only CSV format is currently supported'], 400);
    }

    public function getComparisonData(Request $request)
    {
        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        
        // Get data using local methods
        $dailySalesData = $this->getDailySalesData($startDate, $endDate);
        $mainKitchenData = $this->getMainKitchenData($startDate, $endDate);
        $comparisonData = $this->createComparisonData($dailySalesData, $mainKitchenData);

        return response()->json([
            'success' => true,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'daily_sales' => $dailySalesData,
            'main_kitchen' => $mainKitchenData,
            'comparison' => $comparisonData
        ]);
    }
}