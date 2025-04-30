<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Category;
use App\Models\DailySalesSummary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use DB;

class DailySalesSummaryController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->format('d.m.Y');
        return view('report.daily_summary.index', [
            'today' => $today
        ]);
    }

    public function getSummaryData(Request $request)
{
    try {
        // Parse date from request or use today
        $date = $request->date ? Carbon::createFromFormat('d.m.Y', $request->date) : Carbon::today();
        $formattedDate = $date->format('Y-m-d');
        
        // Log the request for debugging
        Log::info('Getting summary data for date', [
            'date_requested' => $request->date,
            'parsed_date' => $formattedDate
        ]);
        
        // First check if we have saved summaries for this date - QUERY DEBUGGING
        $savedSummaries = DailySalesSummary::where('date', $formattedDate)->get();
        
        Log::info('Saved summaries query result', [
            'query' => 'SELECT * FROM daily_sales_summaries WHERE date = "' . $formattedDate . '"',
            'count' => $savedSummaries->count(),
            'ids' => $savedSummaries->pluck('id')->toArray(),
            'bill_numbers' => $savedSummaries->pluck('bill_number')->toArray(),
            'verified_statuses' => $savedSummaries->pluck('verified', 'bill_number')->toArray(),
        ]);
        
        // Get system sales data for reference
        $startDate = $date->copy()->startOfDay();
        $endDate = $date->copy()->endOfDay();
        $sales = Sale::whereBetween('updated_at', [$startDate, $endDate])->get();
        
        // Initialize the final result array
        $formattedSales = [];
        
        if ($savedSummaries->count() > 0) {
            // Process saved summaries
            foreach ($savedSummaries as $summary) {
                $billNumber = $summary->bill_number;
                
                // Log each summary for debugging
                Log::info('Processing saved summary', [
                    'id' => $summary->id,
                    'bill_number' => $billNumber,
                    'verified' => $summary->verified,
                    'is_manual' => $summary->is_manual
                ]);
                
                $saleData = [
                    'id' => $billNumber,
                    'datetime' => $summary->datetime->format('m/d/Y H:i:s'),
                    'rooms' => (float)$summary->rooms_amount,
                    'swimming_pool' => (float)$summary->swimming_pool_amount,
                    'arrack' => (float)$summary->arrack_amount,
                    'beer' => (float)$summary->beer_amount,
                    'other' => (float)$summary->other_amount,
                    'service_charge' => (float)$summary->service_charge,
                    'description' => $summary->description,
                    'total' => (float)$summary->total_amount,
                    'cash_payment' => (float)$summary->cash_payment,
                    'card_payment' => (float)$summary->card_payment,
                    'bank_payment' => (float)$summary->bank_payment,
                    'status' => $summary->status,
                    'verified' => (bool)$summary->verified, // Make sure to convert to boolean for JS
                    'manual' => (bool)$summary->is_manual,
                ];
                
                $formattedSales[] = $saleData;
            }
            
            // Check if there are any system sales that don't have a summary yet
            $savedBillNumbers = $savedSummaries->pluck('bill_number')->toArray();
            foreach ($sales as $sale) {
                if (!in_array($sale->id, $savedBillNumbers)) {
                    // Add this system sale that doesn't have a summary
                    $categoryTotals = $this->getSaleCategoryTotals($sale->id);
                    
                    $serviceCharge = 0;
                    if (isset($sale->total_recieved) && $sale->total_recieved > 0) {
                        $serviceCharge = (float)$sale->total_recieved;
                    }
                    
                    $formattedSales[] = [
                        'id' => $sale->id,
                        'datetime' => $sale->updated_at->format('m/d/Y H:i:s'),
                        'rooms' => $categoryTotals['rooms'],
                        'swimming_pool' => $categoryTotals['swimming_pool'],
                        'arrack' => $categoryTotals['arrack'],
                        'beer' => $categoryTotals['beer'],
                        'other' => $categoryTotals['other'],
                        'service_charge' => $serviceCharge,
                        'description' => '',
                        'total' => (float)$sale->total_price ?? (float)$sale->change ?? 0,
                        'cash_payment' => 0,
                        'card_payment' => 0,
                        'bank_payment' => 0,
                        'status' => $sale->sale_status,
                        'verified' => false,
                        'manual' => false
                    ];
                }
            }
        } else {
            // No saved data, use the system sales
            foreach ($sales as $sale) {
                $categoryTotals = $this->getSaleCategoryTotals($sale->id);
                
                $serviceCharge = 0;
                if (isset($sale->total_recieved) && $sale->total_recieved > 0) {
                    $serviceCharge = (float)$sale->total_recieved;
                }
                
                $formattedSales[] = [
                    'id' => $sale->id,
                    'datetime' => $sale->updated_at->format('m/d/Y H:i:s'),
                    'rooms' => $categoryTotals['rooms'],
                    'swimming_pool' => $categoryTotals['swimming_pool'],
                    'arrack' => $categoryTotals['arrack'],
                    'beer' => $categoryTotals['beer'],
                    'other' => $categoryTotals['other'],
                    'service_charge' => $serviceCharge,
                    'description' => '',
                    'total' => (float)$sale->total_price ?? (float)$sale->change ?? 0,
                    'cash_payment' => 0,
                    'card_payment' => 0,
                    'bank_payment' => 0,
                    'status' => $sale->sale_status,
                    'verified' => false,
                    'manual' => false
                ];
            }
        }
        
        // Sort the sales by datetime
        usort($formattedSales, function($a, $b) {
            return strtotime($a['datetime']) - strtotime($b['datetime']);
        });
        
        // Log the final formatted sales for debugging
        Log::info('Returning formatted sales', [
            'count' => count($formattedSales),
            'verified_count' => count(array_filter($formattedSales, function($sale) {
                return $sale['verified'] === true;
            }))
        ]);

        return response()->json([
            'sales' => $formattedSales,
            'date' => $date->format('d.m.Y')
        ]);
    } catch (\Exception $e) {
        Log::error('Error in getSummaryData: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);
        
        return response()->json([
            'error' => true,
            'message' => 'Error loading data: ' . $e->getMessage()
        ], 500);
    }
}
    private function getSaleCategoryTotals($saleId)
    {
        try {
            // Calculate totals for each required category
            $roomsCategoryIds = [24, 25, 222, 223]; // Added 222, 223 based on your sales data
            $swimmingPoolCategoryId = [32, 252]; // Added 252 based on your sales data
            $arrackCategoryId = 29;
            $beerCategoryId = [28, 118, 120]; // Added 118, 120 based on your sales data

            // Query to get sales details with category information
            $saleDetails = DB::table('sale_details')
                ->join('menus', 'sale_details.menu_id', '=', 'menus.id')
                ->join('categories', 'menus.category_id', '=', 'categories.id')
                ->where('sale_details.sale_id', $saleId)
                ->select(
                    'categories.id as category_id',
                    'menus.id as menu_id',
                    'menus.name as menu_name',
                    DB::raw('SUM(sale_details.menu_price * sale_details.quantity) as total')
                )
                ->groupBy('categories.id', 'menus.id', 'menus.name')
                ->get();

            // Initialize category totals
            $totals = [
                'rooms' => 0,
                'swimming_pool' => 0,
                'arrack' => 0,
                'beer' => 0,
                'other' => 0,
            ];

            foreach ($saleDetails as $detail) {
                $categoryId = $detail->category_id;
                $menuId = $detail->menu_id;
                $menuName = $detail->menu_name;
                $amount = (float) $detail->total;

                // Categorize based on category ID and menu information
                if (in_array($categoryId, $roomsCategoryIds) || (stripos($menuName, 'room') !== false)) {
                    $totals['rooms'] += $amount;
                } elseif (in_array($categoryId, $swimmingPoolCategoryId) || (stripos($menuName, 'swimming') !== false || stripos($menuName, 'pool') !== false)) {
                    $totals['swimming_pool'] += $amount;
                } elseif ($categoryId == $arrackCategoryId || (stripos($menuName, 'arrack') !== false)) {
                    $totals['arrack'] += $amount;
                } elseif (in_array($categoryId, $beerCategoryId) || (stripos($menuName, 'beer') !== false || stripos($menuName, 'carlsberg') !== false)) {
                    $totals['beer'] += $amount;
                } else {
                    $totals['other'] += $amount;
                }
            }

            return $totals;
        } catch (\Exception $e) {
            Log::error('Error in getSaleCategoryTotals: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'saleId' => $saleId
            ]);
            
            // Return empty totals on error
            return [
                'rooms' => 0,
                'swimming_pool' => 0,
                'arrack' => 0,
                'beer' => 0,
                'other' => 0,
            ];
        }
    }

    
    // Replace your saveSummary method with this modified version that preserves verification status
    
    public function saveSummary(Request $request)
    {
        try {
            // Log what we received
            Log::info('Save summary request received', [
                'date' => $request->date,
                'sales_count' => is_array($request->sales) ? count($request->sales) : 'Not an array'
            ]);
            
            // Parse date correctly using d.m.Y format
            $date = $request->date ? Carbon::createFromFormat('d.m.Y', $request->date) : Carbon::today();
            $salesData = $request->sales ?? [];
            $user = Auth::user();
            
            // Validate that we have sales data
            if (empty($salesData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No sales data provided'
                ]);
            }
            
            // Get existing verification statuses before deleting
            $existingSummaries = DailySalesSummary::where('date', $date->format('Y-m-d'))
                ->get()
                ->keyBy('bill_number');
            
            $existingVerificationStatus = [];
            foreach ($existingSummaries as $billNumber => $summary) {
                $existingVerificationStatus[$billNumber] = $summary->verified;
            }
            
            Log::info('Existing verification statuses', [
                'statuses' => $existingVerificationStatus
            ]);
            
            DB::beginTransaction();
            
            // First, delete any existing summary records for this date
            DailySalesSummary::where('date', $date->format('Y-m-d'))->delete();
            
            // Save each sale summary
            $savedCount = 0;
            $errors = [];
            
            foreach ($salesData as $sale) {
                try {
                    // Parse datetime correctly, ensuring we preserve the original time
                    $saleDateTime = isset($sale['datetime']) && !empty($sale['datetime']) 
                        ? Carbon::parse($sale['datetime']) 
                        : $date;
                    
                    // Get the bill number
                    $billNumber = $sale['id'] ?? '';
                    
                    // Check if we should preserve verification status
                    $isVerified = isset($sale['verified']) ? 
                        (($sale['verified'] === true || $sale['verified'] === 'true' || $sale['verified'] === 1) ? 1 : 0) : 0;
                    
                    // If there was a previously saved verification status, preserve it
                    if (isset($existingVerificationStatus[$billNumber]) && $existingVerificationStatus[$billNumber] == 1) {
                        $isVerified = 1;
                    }
                    
                    $isManual = isset($sale['manual']) ? 
                        (($sale['manual'] === true || $sale['manual'] === 'true' || $sale['manual'] === 1) ? 1 : 0) : 0;
                    
                    // Create record
                    $summary = new DailySalesSummary([
                        'date' => $date->format('Y-m-d'),
                        'sale_id' => $isManual ? null : $billNumber,
                        'bill_number' => $billNumber,
                        'datetime' => $saleDateTime, // Use the actual timestamp
                        'rooms_amount' => $sale['rooms'] ?? 0,
                        'swimming_pool_amount' => $sale['swimming_pool'] ?? 0,
                        'arrack_amount' => $sale['arrack'] ?? 0,
                        'beer_amount' => $sale['beer'] ?? 0,
                        'other_amount' => $sale['other'] ?? 0,
                        'service_charge' => $sale['service_charge'] ?? 0,
                        'description' => $sale['description'] ?? '',
                        'total_amount' => $sale['total'] ?? 0,
                        'cash_payment' => $sale['cash_payment'] ?? 0,
                        'card_payment' => $sale['card_payment'] ?? 0,
                        'bank_payment' => $sale['bank_payment'] ?? 0,
                        'status' => $sale['status'] ?? 'unpaid',
                        'verified' => $isVerified, // Use preserved verification status
                        'is_manual' => $isManual,
                        'created_by' => $user ? $user->id : null,
                        'updated_by' => $user ? $user->id : null,
                    ]);
                    
                    $summary->save();
                    
                    $savedCount++;
                    
                    Log::info('Saved sale summary', [
                        'bill_number' => $billNumber,
                        'date' => $date->format('Y-m-d'),
                        'datetime' => $saleDateTime->format('Y-m-d H:i:s'),
                        'summary_id' => $summary->id,
                        'verified' => $isVerified
                    ]);
                } catch (\Exception $innerE) {
                    $errors[] = $innerE->getMessage();
                    Log::error('Error saving individual sale: ' . $innerE->getMessage(), [
                        'sale' => $sale,
                        'trace' => $innerE->getTraceAsString()
                    ]);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Successfully saved $savedCount records",
                'saved_count' => $savedCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error saving summary: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error saving summary: ' . $e->getMessage()
            ], 500);
        }
    }

    public function printSummary(Request $request)
    {
        try {
            // Parse date from request using correct format
            $date = $request->date ? Carbon::createFromFormat('d.m.Y', $request->date) : Carbon::today();
            $formattedDate = $date->format('Y-m-d');
            
            // Get system sales data for reference
            $startDate = $date->copy()->startOfDay();
            $endDate = $date->copy()->endOfDay();
    
            // Query to get all sales for the date range
            $systemSales = Sale::whereBetween('updated_at', [$startDate, $endDate])->get()->keyBy('id');
            
            // Check if we have saved summaries for this date
            $savedSummaries = DailySalesSummary::where('date', $formattedDate)->get();
            
            if ($savedSummaries->count() > 0) {
                // Use saved summaries
                $formattedSales = $savedSummaries->map(function($summary) use ($systemSales) {
                    $billNumber = $summary->bill_number;
                    
                    // Get service charge from system if available
                    $serviceCharge = (float)$summary->service_charge;
                    if ($serviceCharge <= 0 && isset($systemSales[$billNumber])) {
                        $serviceCharge = (float)$systemSales[$billNumber]->total_recieved;
                    }
                    
                    return [
                        'id' => $summary->bill_number,
                        'datetime' => $summary->datetime->format('m/d/Y H:i:s'),
                        'rooms' => (float)$summary->rooms_amount,
                        'swimming_pool' => (float)$summary->swimming_pool_amount,
                        'arrack' => (float)$summary->arrack_amount,
                        'beer' => (float)$summary->beer_amount,
                        'other' => (float)$summary->other_amount,
                        'service_charge' => $serviceCharge,
                        'description' => $summary->description,
                        'total' => (float)$summary->total_amount,
                        'cash_payment' => (float)$summary->cash_payment,
                        'card_payment' => (float)$summary->card_payment,
                        'bank_payment' => (float)$summary->bank_payment,
                        'status' => $summary->status,
                        'verified' => $summary->verified ? 1 : 0,
                    ];
                })->toArray();
            } else {
                // Generate from sales data if no saved summaries exist
                $formattedSales = [];
        
                foreach ($systemSales as $sale) {
                    $categoryTotals = $this->getSaleCategoryTotals($sale->id);
                    
                    $formattedSale = [
                        'id' => $sale->id,
                        'datetime' => $sale->updated_at->format('m/d/Y H:i:s'),
                        'rooms' => $categoryTotals['rooms'],
                        'swimming_pool' => $categoryTotals['swimming_pool'],
                        'arrack' => $categoryTotals['arrack'],
                        'beer' => $categoryTotals['beer'],
                        'other' => $categoryTotals['other'],
                        'service_charge' => (float)$sale->total_recieved,
                        'description' => '', // Empty by default
                        'total' => (float)$sale->total_price ?? (float)$sale->change ?? 0,
                        'cash_payment' => 0, 
                        'card_payment' => 0, 
                        'bank_payment' => 0, 
                        'status' => $sale->sale_status, 
                        'verified' => 0, 
                    ];
        
                    $formattedSales[] = $formattedSale;
                }
            }

            return view('report.daily_summary.print', [
                'sales' => $formattedSales,
                'date' => $date->format('d.m.Y')
            ]);
        } catch (\Exception $e) {
            Log::error('Error in printSummary: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return back()->with('error', 'Error generating print view: ' . $e->getMessage());
        }
    }
    

     // Add this new method for handling verification
     
     // Replace your current toggleVerify method with this updated version
     
     public function toggleVerify(Request $request)
     {
         try {
             // Log the incoming request for debugging
             Log::info('Toggle verify request received', [
                 'request_data' => $request->all(),
                 'method' => $request->method(),
                 'headers' => $request->headers->all(),
             ]);
             
             $billNumber = $request->bill_number;
             $verified = $request->verified ? 1 : 0;
             $date = $request->date ? Carbon::createFromFormat('d.m.Y', $request->date)->format('Y-m-d') : Carbon::today()->format('Y-m-d');
             
             Log::info('Processing verification with parsed data', [
                 'bill_number' => $billNumber,
                 'verified' => $verified,
                 'date' => $date
             ]);
             
             if (empty($billNumber)) {
                 Log::error('Bill number is empty');
                 return response()->json([
                     'success' => false,
                     'message' => 'Bill number is required'
                 ], 400);
             }
             
             // Find the summary record
             $summary = DailySalesSummary::where('bill_number', $billNumber)
                 ->where('date', $date)
                 ->first();
             
             Log::info('Found summary record?', ['found' => !is_null($summary)]);
                 
             if (!$summary) {
                 // If no saved record exists, try to find the sale in the system
                 $sale = Sale::find($billNumber);
                 
                 if (!$sale) {
                     Log::error('Sale not found', ['bill_number' => $billNumber]);
                     return response()->json([
                         'success' => false,
                         'message' => 'Bill not found'
                     ], 404);
                 }
                 
                 // Log sale data
                 Log::info('Found sale record', [
                     'sale_id' => $sale->id,
                     'updated_at' => $sale->updated_at
                 ]);
                 
                 // Get category totals for this sale
                 $categoryTotals = $this->getSaleCategoryTotals($sale->id);
                 
                 // Get service charge from database
                 $serviceCharge = 0;
                 if (isset($sale->total_recieved) && $sale->total_recieved > 0) {
                     $serviceCharge = (float)$sale->total_recieved;
                 }
                 
                 // Create a new summary record
                 $summary = new DailySalesSummary([
                     'date' => $date,
                     'sale_id' => $sale->id,
                     'bill_number' => $sale->id,
                     'datetime' => $sale->updated_at,
                     'rooms_amount' => $categoryTotals['rooms'],
                     'swimming_pool_amount' => $categoryTotals['swimming_pool'],
                     'arrack_amount' => $categoryTotals['arrack'],
                     'beer_amount' => $categoryTotals['beer'],
                     'other_amount' => $categoryTotals['other'],
                     'service_charge' => $serviceCharge,
                     'description' => '',
                     'total_amount' => (float)$sale->total_price ?? (float)$sale->change ?? 0,
                     'cash_payment' => 0,
                     'card_payment' => 0,
                     'bank_payment' => 0,
                     'status' => $sale->sale_status,
                     'verified' => $verified,
                     'is_manual' => 0,
                     'created_by' => Auth::id(),
                     'updated_by' => Auth::id(),
                 ]);
                 
                 Log::info('Created new summary record', [
                     'summary_data' => $summary->toArray()
                 ]);
             } else {
                 // Update the verification status
                 $summary->verified = $verified;
                 $summary->updated_by = Auth::id();
                 
                 Log::info('Updated existing summary record', [
                     'summary_id' => $summary->id,
                     'new_verified_value' => $verified
                 ]);
             }
             
             // Save the record
             $saved = $summary->save();
             
             Log::info('Save result', ['saved' => $saved]);
             
             // Double-check the record was saved correctly
             $verifiedSummary = DailySalesSummary::find($summary->id);
             Log::info('Verification after save', [
                 'summary_id' => $summary->id,
                 'verified_value' => $verifiedSummary ? $verifiedSummary->verified : 'record not found',
                 'record_exists' => !is_null($verifiedSummary)
             ]);
             
             return response()->json([
                 'success' => true,
                 'message' => $verified ? 'Bill verified successfully' : 'Bill unverified successfully',
                 'bill_number' => $billNumber,
                 'verified' => $verified,
                 'summary_id' => $summary->id
             ]);
         } catch (\Exception $e) {
             Log::error('Error in toggleVerify: ' . $e->getMessage(), [
                 'trace' => $e->getTraceAsString(),
                 'request' => $request->all()
             ]);
             
             return response()->json([
                 'success' => false,
                 'message' => 'Error toggling verification: ' . $e->getMessage()
             ], 500);
         }
     }
 
     public function debugVerify(Request $request)
     {
         try {
             $requestData = $request->all();
             
             Log::info('Debug Verify Request', [
                 'method' => $request->method(),
                 'data' => $requestData,
                 'headers' => $request->headers->all(),
                 'ip' => $request->ip(),
                 'ajax' => $request->ajax(),
                 'path' => $request->path(),
             ]);
             
             // Try to get the POST data directly from php://input as a fallback
             $rawInput = file_get_contents('php://input');
             Log::info('Raw Input', ['data' => $rawInput]);
             
             return response()->json([
                 'success' => true,
                 'message' => 'Debug data logged',
                 'request_data' => $requestData,
                 'raw_input' => $rawInput
             ]);
         } catch (\Exception $e) {
             Log::error('Error in debugVerify: ' . $e->getMessage(), [
                 'trace' => $e->getTraceAsString()
             ]);
             
             return response()->json([
                 'success' => false,
                 'message' => 'Error: ' . $e->getMessage()
             ], 500);
         }
     }


    // Debug method to help troubleshoot missing bills
    public function checkBills(Request $request)
    {
        try {
            $date = $request->date ? Carbon::createFromFormat('d.m.Y', $request->date) : Carbon::today();
            $startDate = $date->copy()->startOfDay();
            $endDate = $date->copy()->endOfDay();
            
            // Get ALL sales from the specified date
            $sales = Sale::whereBetween('updated_at', [$startDate, $endDate])->get();
            
            // Get saved summaries
            $savedSummaries = DailySalesSummary::where('date', $date->format('Y-m-d'))->get();
            
            // Get list of bill numbers in saved summaries
            $savedBillNumbers = $savedSummaries->pluck('bill_number')->toArray();
            
            return response()->json([
                'date' => $date->format('Y-m-d'),
                'count' => $sales->count(),
                'saved_summaries_count' => $savedSummaries->count(),
                'sales' => $sales->map(function($sale) use ($savedBillNumbers) {
                    return [
                        'id' => $sale->id,
                        'date' => $sale->updated_at->format('Y-m-d H:i:s'),
                        'status' => $sale->sale_status,
                        'total' => $sale->total_price ?? $sale->change ?? 0,
                        'service_charge' => $sale->total_recieved ?? 0,
                        'table' => $sale->table_name,
                        'in_summary' => in_array((string)$sale->id, $savedBillNumbers)
                    ];
                }),
                'saved_summaries' => $savedSummaries->map(function($summary) {
                    return [
                        'id' => $summary->id,
                        'bill_number' => $summary->bill_number,
                        'date' => $summary->date->format('Y-m-d'),
                        'datetime' => $summary->datetime->format('Y-m-d H:i:s'),
                        'total' => $summary->total_amount,
                        'service_charge' => $summary->service_charge,
                        'status' => $summary->status,
                        'is_manual' => $summary->is_manual
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}