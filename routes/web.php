<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PettycashController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvInventoryController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\CostController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskCategoryController;
use App\Http\Controllers\CompletedTaskController;
use App\Http\Controllers\RoomAvailabilityController;
use App\Http\Controllers\StaffAttendanceController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\VehicleSecurityController;
use App\Http\Controllers\ManualAttendanceController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PackageCategoryController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\DamageItemController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\ServiceChargeController;
use App\Http\Controllers\CashierBalanceController;
use App\Http\Controllers\SalesSummaryController;
use App\Http\Controllers\KitchenOrderController;
use App\Http\Controllers\LenderController;
use App\Http\Controllers\RoomAvailabilityVisualizerController;
use App\Http\Controllers\LeaveRequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);

Auth::routes(['register' =>false, 'reset' =>false ]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::middleware(['auth'])->group(function(){
    
     //routes for cashier
     Route::get('/cashier', 'App\Http\Controllers\Cashier\CashierController@index')->name('cashier');
     Route::get('/cashier/getMenuByCategory/{category_id}/{search_keyword?}', 'App\Http\Controllers\Cashier\CashierController@getMenuByCategory');
     
     
     Route::get('/cashier/getTable', 'App\Http\Controllers\Cashier\CashierController@getTables');
     Route::get('/cashier/getSaleDetailsByTable/{table_id}', 'App\Http\Controllers\Cashier\CashierController@getSaleDetailsByTable');
     
     
     Route::post('/cashier/orderFood', 'App\Http\Controllers\Cashier\CashierController@orderFood');
     
     Route::post('/cashier/deleteSaleDetail', 'App\Http\Controllers\Cashier\CashierController@deleteSaleDetail');
     Route::post('/cashier/increase-quantity', 'App\Http\Controllers\Cashier\CashierController@increaseQuantity');
     Route::post('/cashier/change-quantity', 'App\Http\Controllers\Cashier\CashierController@changesQuantity');
     

     
     Route::post('/cashier/decrease-quantity', 'App\Http\Controllers\Cashier\CashierController@decreaseQuantity');
     
     Route::post('/cashier/confirmOrderStatus','App\Http\Controllers\Cashier\CashierController@confirmOrderStatus' );
     
     Route::post('/cashier/savePayment', 'App\Http\Controllers\Cashier\CashierController@savePayment');
     Route::get('/cashier/showRecipt/{saleID}', 'App\Http\Controllers\Cashier\CashierController@showRecipt');
     

     Route::get('/cashier/printOrderRec/{saleID}', 'App\Http\Controllers\Cashier\CashierController@printOrderRec');

     Route::post('/cashier/printOrder', 'App\Http\Controllers\Cashier\CashierController@printOrder');
     

// Advance Payment Routes
Route::get('/cashier/advance-payment/{saleID}', 'App\Http\Controllers\Cashier\CashierController@showAdvancePaymentSelection')
    ->name('cashier.advancePaymentSelection');
Route::get('/cashier/advance-payment/{saleID}/{type}', 'App\Http\Controllers\Cashier\CashierController@showAdvancePaymentForm')
    ->name('cashier.advancePaymentForm');
Route::post('/cashier/advance-payment/submit', 'App\Http\Controllers\Cashier\CashierController@submitAdvancePayment')
    ->name('cashier.submitAdvancePayment');

   Route::get('/cashier/showAdvanceRecipt/{saleID}', 'App\Http\Controllers\Cashier\CashierController@showAdvanceRecipt')
    ->name('cashier.showAdvanceRecipt');
Route::get('/cashier/showAdvanceWeddingRecipt/{saleID}', 'App\Http\Controllers\Cashier\CashierController@showAdvanceWeddingRecipt')
    ->name('cashier.showAdvanceWeddingRecipt');

    Route::get('/cashier/setup-advance-payment/{table_id}', 'App\Http\Controllers\Cashier\CashierController@setupAdvancePayment')
    ->name('cashier.setupAdvancePayment');

    Route::get('pettycash', 'App\Http\Controllers\PettycashController@index')->name('pettycash');
    Route::post('pettycash/store', 'App\Http\Controllers\PettycashController@store')->name(('pettycash.store'));
    Route::get('pettycash/destroy/{id}', 'App\Http\Controllers\PettycashController@destroy')->name(('pettycash.destroy'));

});Route::get('/management',function(){
    return view('management.index'); 
 })->name('management');
 
 

 
 // routes for management
 Route::resource('management/category', App\Http\Controllers\Management\CategoryController::class);
 Route::resource('management/menu', App\Http\Controllers\Management\MenuController::class);
 Route::resource('management/table', App\Http\Controllers\Management\TableController::class);

 // route for inventory
 Route::get('/inventory',function(){
    return view('inventory.index'); 
 })->name('inventory');

 // routes for inventory
Route::resource('inventory/category', App\Http\Controllers\Inventory\CategoryController::class);
Route::resource('inventory/menu', App\Http\Controllers\Inventory\MenuController::class);
Route::resource('inventory/stock', App\Http\Controllers\Inventory\StockController::class);

Route::post('inventory/stockFilterByCategory', 'App\Http\Controllers\Inventory\StockController@index')->name('Stock.stockFilterByCategory');

Route::get('inventory/stock/{itemid}', 'App\Http\Controllers\Inventory\StockController@show')->name('Stock.show');
Route::get('/stock/monthly', [InventoryController::class, 'viewMonthlyStock'])->name('stock.monthly');

Route::resource('inventory/table', App\Http\Controllers\Inventory\TableController::class);

Route::get('/categories-products', [InventoryController::class, 'categoriesProducts'])
    ->name('categories-products.index');


// Routes for merged products management (add to the routes/web.php file)
Route::middleware(['auth'])->group(function() {
    Route::get('/inventory/merged-products', 'App\Http\Controllers\Inventory\MergedProductController@index')->name('merged-products.index');
    Route::post('/inventory/merged-products/merge', 'App\Http\Controllers\Inventory\MergedProductController@merge')->name('merged-products.merge');
    Route::get('/inventory/merged-products/unmerge/{parentId}', 'App\Http\Controllers\Inventory\MergedProductController@unmerge')->name('merged-products.unmerge');
    Route::get('/inventory/merged-products/consolidate/{parentId}', 'App\Http\Controllers\Inventory\MergedProductController@consolidate')->name('merged-products.consolidate');
    Route::post('/inventory/merged-products/redistribute', 'App\Http\Controllers\Inventory\MergedProductController@redistribute')->name('merged-products.redistribute');
    // Remove the middleware group to make sure it's publicly accessible
Route::get('/inventory/stock/daily-sales', 'App\Http\Controllers\Inventory\StockController@getDailySalesData')
->name('stock.daily-sales');

// And add this alternative route as a backup (with a different URL pattern)
Route::get('/api/daily-sales', 'App\Http\Controllers\Inventory\StockController@getDailySalesData')
->name('api.daily-sales');

    Route::get('/test-daily-sales/{date?}', function($date = null) {
        try {
            if (!$date) {
                $date = \Carbon\Carbon::today()->format('Y-m-d');
            }
            
            $controller = new \App\Http\Controllers\Inventory\StockController();
            $request = new \Illuminate\Http\Request();
            $request->merge(['date' => $date]);
            
            return $controller->getDailySalesData($request);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    });

});





// routes for calender
Route::get('/calendar', function () {
   return view('calendar');
})->name('calendar');
Route::get('/bookings', [BookingController::class, 'index']);
Route::post('/bookings', [BookingController::class, 'store']);
Route::put('/bookings/{id}', [BookingController::class, 'update']);
Route::get('/available-rooms', [BookingController::class, 'availableRooms']);
Route::get('/booking-logs', [BookingController::class, 'getLogs']);
Route::get('/bookings/{id}/print', [BookingController::class, 'printConfirmation'])->name('bookings.print');

// routes/web.php
Route::post('/booking-payments/{payment}/toggle-verification', 
    [BookingController::class, 'toggleVerification'])
    ->middleware('auth')
    ->name('booking-payments.toggle-verification');

// Add these routes to your existing web.php file

// Add these routes to your existing routes/web.php file
// Replace the existing stock management routes with these updated ones

// Stock Management Routes (Updated for Category-specific tracking)
Route::middleware(['auth'])->group(function () {
    // Main stock management routes
    Route::get('/stock', [InventoryController::class, 'index'])->name('stock.index');
    Route::post('/stock', [InventoryController::class, 'store'])->name('stock.store');
    Route::post('/stock/update', [InventoryController::class, 'updateTodayStock'])->name('stock.update');
    
    // Category and item management
    Route::post('/categories', [InventoryController::class, 'storeCategory'])->name('categories.store');
    Route::post('/items', [InventoryController::class, 'storeItem'])->name('items.store');
    Route::get('/categories-products', [InventoryController::class, 'categoriesProducts'])->name('categories-products.index');
    
    // Stock viewing and testing
    Route::get('/stock/monthly', [InventoryController::class, 'viewMonthlyStock'])->name('stock.monthly');
    Route::post('/stock/monthly', [InventoryController::class, 'viewMonthlyStock'])->name('stock.monthly.post');
    Route::get('/stock/test-propagation', [InventoryController::class, 'checkStockPropagation'])->name('stock.test-propagation');
    
    // Usage reports and exports (New)
    Route::get('/stock/usage-report', [InventoryController::class, 'getUsageReport'])->name('stock.usage-report');
    Route::get('/stock/export-usage', [InventoryController::class, 'exportUsageReport'])->name('stock.export-usage');
    
    // Live data updates (New)
    Route::get('/stock/live-data', [InventoryController::class, 'getLiveStockData'])->name('stock.live-data');
});

// routes for inventory for physical Items
// Inventory Dashboard
Route::get('/inv-inventory', [InvInventoryController::class, 'index'])->name('inv_inventory.index');
// Store Category
Route::post('/inv-inventory/categories', [InvInventoryController::class, 'storeCategory'])->name('inv_inventory.categories.store');
// Store Product
Route::post('/inv-inventory/products', [InvInventoryController::class, 'storeProduct'])->name('inv_inventory.products.store');
// Update Today's Stock
Route::post('/inv-inventory/update', [InvInventoryController::class, 'updateTodayStock'])->name('inv_inventory.update');
// View Monthly Stock
Route::get('/inv-inventory/monthly', [InvInventoryController::class, 'viewMonthlyStock'])->name('inv_inventory.monthly');


// Add these routes to your web.php file in the routes for reports section

// Routes for daily sales summary
Route::get('/report/daily-summary', 'App\Http\Controllers\Report\DailySalesSummaryController@index')->name('report.daily-summary');
Route::get('/report/daily-summary/data', 'App\Http\Controllers\Report\DailySalesSummaryController@getSummaryData')->name('report.daily-summary.data');
Route::post('/report/daily-summary/save', 'App\Http\Controllers\Report\DailySalesSummaryController@saveSummary')->name('report.daily-summary.save');
Route::get('/report/daily-summary/print', 'App\Http\Controllers\Report\DailySalesSummaryController@printSummary')->name('report.daily-summary.print');
Route::get('/report/check-bills/{date}', 'App\Http\Controllers\Report\DailySalesSummaryController@checkBillsForDate');
Route::post('/report/daily-summary/toggle-verify', 'App\Http\Controllers\Report\DailySalesSummaryController@toggleVerify')->name('report.daily-summary.toggle-verify');
// Add this route to your web.php file to help debug missing bills
Route::get('/report/check-bills', 'App\Http\Controllers\Report\DailySalesSummaryController@checkBills')->name('report.check-bills');
Route::post('/report/daily-summary/debug-verify', 'App\Http\Controllers\Report\DailySalesSummaryController@debugVerify');
Route::get('/report/daily-summary/logs', 'App\Http\Controllers\Report\DailySalesSummaryController@getLogData')->name('report.daily-summary.logs');


// Vehicle Security Routes
Route::middleware(['auth'])->group(function () {

    Route::get('vehicle-security/dashboard-stats', [VehicleSecurityController::class, 'getDashboardStats'])
    ->name('vehicle-security.dashboard-stats');
    Route::post('vehicle-security/reset-rooms', [VehicleSecurityController::class, 'resetRoomStatus'])
    ->name('vehicle-security.reset-rooms');
   Route::resource('vehicle-security', VehicleSecurityController::class);
   Route::get('vehicle-security/date/{date?}', [VehicleSecurityController::class, 'showByDate'])
    ->name('vehicle-security.by-date');
    Route::post('vehicle-security/{id}/checkout', [VehicleSecurityController::class, 'checkout'])->name('vehicle-security.checkout');
    Route::post('vehicle-security/{id}/temp-checkout', [VehicleSecurityController::class, 'tempCheckout'])->name('vehicle-security.temp-checkout');
    Route::post('vehicle-security/{id}/temp-checkin', [VehicleSecurityController::class, 'tempCheckin'])->name('vehicle-security.temp-checkin');
    Route::post('vehicle-security/{id}/update-team', [VehicleSecurityController::class, 'updateTeam'])->name('vehicle-security.update-team');
    Route::get('vehicle-security/available-rooms', [VehicleSecurityController::class, 'getAvailableRooms'])
    ->name('vehicle-security.available-rooms');
    Route::put('vehicle-security/{id}/update', [VehicleSecurityController::class, 'update'])->name('vehicle-security.update');
});




// Expenses
Route::middleware(['auth'])->group(function () {
   // Resource routes for groups and persons
   Route::resource('groups', GroupController::class);
   Route::resource('persons', PersonController::class);
   
   // Costs routes with print functionality
   Route::resource('costs', CostController::class)->except(['show']);
   Route::get('costs/print-daily', [CostController::class, 'printDailyExpenses'])->name('costs.print.daily');
   Route::get('costs/{cost}/print', [CostController::class, 'printTransaction'])->name('costs.print.transaction');
});
// Task
// Home Route

// Task Routes
Route::resource('tasks', TaskController::class);
Route::post('tasks/{id}/update-status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
// Task Category Routes
Route::resource('task-categories', TaskCategoryController::class);
Route::get('completed-tasks', [CompletedTaskController::class, 'index'])->name('completed-tasks.index');


Route::middleware(['auth'])->group(function () {
   Route::get('/staff/attendance', [StaffAttendanceController::class, 'index'])->name('staff.attendance.index');
   Route::post('/staff/attendance', [StaffAttendanceController::class, 'store'])->name('staff.attendance.store');
   Route::post('/staff/attendance/checkout', [StaffAttendanceController::class, 'checkOut'])->name('staff.attendance.checkout');
   Route::get('/staff/attendance/report', [StaffAttendanceController::class, 'report'])->name('staff.attendance.report');
});


// routes/api.php
Route::post('/fingerprint/attendance', [FingerprintDeviceController::class, 'processAttendance']);


Route::middleware(['web', 'auth'])->group(function () {
   Route::post('/staff/attendance/import', [StaffAttendanceController::class, 'import'])->name('staff.attendance.import');
});

//new attendance


// Then update the routes to include the full namespace:
// Manual Attendance Routes
// Manual Attendance Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/manual-attendance', [ManualAttendanceController::class, 'index'])->name('attendance.manual.index');
    Route::post('/manual-attendance/mark', [ManualAttendanceController::class, 'markAttendance'])->name('attendance.manual.mark');
    Route::post('/manual-attendance/toggle', [ManualAttendanceController::class, 'toggleAttendance'])->name('attendance.manual.toggle');
    Route::get('/manual-attendance/staff/{personId}/history', [ManualAttendanceController::class, 'getStaffAttendanceHistory'])->name('attendance.manual.staff.history');
    Route::get('/manual-attendance/report', [ManualAttendanceController::class, 'report'])->name('attendance.manual.report');
    
    // Add these two routes for the staff member functionality
    Route::post('/manual-attendance/search-persons', [ManualAttendanceController::class, 'searchPersons'])->name('attendance.manual.search-persons');
    Route::post('/manual-attendance/add-staff', [ManualAttendanceController::class, 'addStaffMember'])->name('attendance.manual.add-staff');
    Route::get('/manual-attendance/add-staff', [ManualAttendanceController::class, 'showAddStaffForm'])->name('attendance.manual.add-staff-form');

Route::get('/manual-attendance/manage-categories', [ManualAttendanceController::class, 'showManageCategories'])
        ->name('attendance.manual.manage-categories');
    
    // Route for updating a single staff category
    Route::post('/manual-attendance/update-category', [ManualAttendanceController::class, 'updateStaffCategory'])
        ->name('attendance.manual.update-category');
    
    // Route for bulk updating staff categories
    Route::post('/manual-attendance/bulk-update-categories', [ManualAttendanceController::class, 'bulkUpdateCategories'])
        ->name('attendance.manual.bulk-update-categories');
        

});

// Leave Request Routes - Add this complete section to your web.php file
Route::middleware(['auth'])->group(function () {
    // Custom routes that need to be defined BEFORE resource routes to avoid conflicts
    
    // Status update route (for approve/reject functionality)
    Route::post('/leave-requests/update-status/{leaveRequest}', [LeaveRequestController::class, 'updateStatus'])
        ->name('leave-requests.status');
    
    // Print route (for printing leave request details)
    Route::get('/leave-requests/{leaveRequest}/print', [LeaveRequestController::class, 'print'])
        ->name('leave-requests.print');
    
    // Calendar view route
    Route::get('/leave-requests-calendar', [LeaveRequestController::class, 'calendar'])
        ->name('leave-requests.calendar');
    
    // Calendar data API route (for AJAX calendar data)
    Route::get('/leave-requests/calendar-data', [LeaveRequestController::class, 'getCalendarData'])
        ->name('leave-requests.calendar-data');
    
    // Main leave request resource routes (MUST come AFTER the custom routes above)
    Route::resource('leave-requests', LeaveRequestController::class);
});

// Gate Pass Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard route
    Route::get('/gate-passes/dashboard', [App\Http\Controllers\GatePassController::class, 'dashboard'])
        ->name('gate-passes.dashboard');
    
    // Status update route (for approve/reject functionality)
    Route::post('/gate-passes/update-status/{gatePass}', [App\Http\Controllers\GatePassController::class, 'updateStatus'])
        ->name('gate-passes.update-status');
    
    // Mark return route
    Route::post('/gate-passes/{gatePass}/mark-return', [App\Http\Controllers\GatePassController::class, 'markReturn'])
        ->name('gate-passes.mark-return');
    
    // Print route
    Route::get('/gate-passes/{gatePass}/print', [App\Http\Controllers\GatePassController::class, 'print'])
        ->name('gate-passes.print');
    
    // Main gate pass resource routes
    Route::resource('gate-passes', App\Http\Controllers\GatePassController::class);
});


// Kitchen Display System Routes
Route::middleware(['auth'])->prefix('kitchen')->group(function () {
    // Main kitchen display
    Route::get('/', [KitchenOrderController::class, 'index'])->name('kitchen.index');
    
    // Order management routes
    Route::put('/orders/{id}/status', [KitchenOrderController::class, 'updateOrderStatus']);
    Route::put('/items/{id}/status', [KitchenOrderController::class, 'updateItemStatus']);
    Route::get('/orders/filter', [KitchenOrderController::class, 'getOrdersBySource']);
    
    // Event routes
    Route::get('/yesterday-events', [KitchenOrderController::class, 'getYesterdayEvents']);
    Route::get('/today-events', [KitchenOrderController::class, 'getTodayEvents']);
    Route::get('/tomorrow-events', [KitchenOrderController::class, 'getTomorrowEvents']);
    Route::get('/day3-events', [KitchenOrderController::class, 'getDay3Events']);
    Route::get('/day4-events', [KitchenOrderController::class, 'getDay4Events']);
    Route::get('/day5-events', [KitchenOrderController::class, 'getDay5Events']);
    Route::get('/day6-events', [KitchenOrderController::class, 'getDay6Events']);
    
    // Other routes
    Route::get('/menus', [KitchenOrderController::class, 'getMenus']);
    Route::get('/analytics', [KitchenOrderController::class, 'getAnalyticsData']);
});


// Add these routes to your existing web.php file

// Kitchen Comparison Routes - Add this section to your web.php file
Route::middleware(['auth'])->group(function () {
    // Main kitchen comparison page
    Route::get('/kitchen/comparison', [App\Http\Controllers\KitchenComparisonController::class, 'index'])
        ->name('kitchen.comparison');
    
    // AJAX endpoint for getting comparison data
    Route::get('/kitchen/comparison/data', [App\Http\Controllers\KitchenComparisonController::class, 'getComparisonData'])
        ->name('kitchen.comparison.data');
    
    // Export comparison report
    Route::get('/kitchen/comparison/export', [App\Http\Controllers\KitchenComparisonController::class, 'exportComparison'])
        ->name('kitchen.comparison.export');
});



// Package Management Routes
Route::middleware(['auth'])->group(function () {
   Route::resource('package-categories', PackageCategoryController::class);
   Route::resource('packages', PackageController::class);
   Route::get('/packages/{package}/print', [PackageController::class, 'print'])->name('packages.print');
});

// Quotation Management Routes
Route::middleware(['auth'])->group(function () {
   // Existing quotation resource routes
   Route::resource('quotations', QuotationController::class);
   
   // Add these new routes for quotation conversion and printing
   Route::post('/quotations/{quotation}/convert', [QuotationController::class, 'convertToBooking'])
       ->name('quotations.convert-to-booking');
   Route::get('/quotations/{quotation}/print', [QuotationController::class, 'print'])
       ->name('quotations.print');
});


// Lenders/Creditors Management Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/lenders', [App\Http\Controllers\LenderController::class, 'index'])->name('lenders.index');
    Route::post('/lenders', [App\Http\Controllers\LenderController::class, 'store'])->name('lenders.store');
    Route::get('/lenders/{id}/mark-paid', [App\Http\Controllers\LenderController::class, 'markAsPaid'])->name('lenders.mark-paid');
    Route::delete('/lenders/{id}', [App\Http\Controllers\LenderController::class, 'destroy'])->name('lenders.destroy');
    Route::resource('lenders', LenderController::class);
    Route::get('/lenders/{id}/mark-paid', [App\Http\Controllers\LenderController::class, 'markAsPaid'])
    ->name('lenders.mark-paid');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/food-menu', [App\Http\Controllers\FoodMenuController::class, 'index'])->name('food-menu.index');
    Route::get('/food-menu/bookings', [App\Http\Controllers\FoodMenuController::class, 'getBookingsForDate'])->name('food-menu.bookings');
    Route::post('/food-menu/save', [App\Http\Controllers\FoodMenuController::class, 'saveMenu'])->name('food-menu.save');
    Route::get('/food-menu/print/{booking}/{date}', [App\Http\Controllers\FoodMenuController::class, 'printMenu'])->name('food-menu.print');
});

// Add this to your food menu routes
Route::get('/food-menu/print-daily', [App\Http\Controllers\FoodMenuController::class, 'printDailyMenus'])->name('food-menu.print-daily');

// Add this to your routes/web.php file for testing the bookings endpoint

Route::get('/test-food-menu-bookings', function() {
    try {
        $date = request('date', now()->format('Y-m-d'));
        $day = \Carbon\Carbon::parse($date);
        
        // Find bookings that are active on the selected date
        $bookings = \App\Models\Booking::where(function ($query) use ($day) {
            $query->where(function ($q) use ($day) {
                // Bookings with defined end date that include this day
                $q->whereDate('start', '<=', $day->format('Y-m-d'))
                    ->whereDate('end', '>=', $day->format('Y-m-d'))
                    ->whereNotNull('end');
                    
                // OR single day bookings on this day
                $q->orWhere(function($sq) use ($day) {
                    $sq->whereDate('start', $day->format('Y-m-d'))
                        ->where(function($ssq) {
                            $ssq->whereNull('end')
                                ->orWhere('end', 'N/A')
                                ->orWhere('end', '');
                        });
                });
            });
        })
        ->get();
        
        // Transform the bookings to include formatted details
        $formattedBookings = $bookings->map(function ($booking) use ($day) {
            return [
                'id' => $booking->id,
                'title' => $booking->name,
                'function_type' => $booking->function_type,
                'contact_number' => $booking->contact_number,
                'guest_count' => $booking->guest_count,
                'room_numbers' => json_encode($booking->room_numbers),
                'start' => $booking->start ? $booking->start->format('Y-m-d H:i:s') : null,
                'end' => $booking->end ? $booking->end->format('Y-m-d H:i:s') : null,
                'formatted_start' => $booking->start ? $booking->start->format('h:i A') : null,
                'formatted_end' => $booking->end ? $booking->end->format('h:i A') : null,
                // Include full booking for debugging
                'full_booking' => $booking->toArray()
            ];
        });
        
        // Get raw SQL query for debugging
        $bindings = [];
        $sql = \App\Models\Booking::where(function ($query) use ($day) {
            $query->where(function ($q) use ($day) {
                $q->whereDate('start', '<=', $day->format('Y-m-d'))
                    ->whereDate('end', '>=', $day->format('Y-m-d'))
                    ->whereNotNull('end');
                    
                $q->orWhere(function($sq) use ($day) {
                    $sq->whereDate('start', $day->format('Y-m-d'))
                        ->where(function($ssq) {
                            $ssq->whereNull('end')
                                ->orWhere('end', 'N/A')
                                ->orWhere('end', '');
                        });
                });
            });
        })->toSql();
        
        return response()->json([
            'date' => $day->format('Y-m-d'),
            'formatted_date' => $day->format('F j, Y'),
            'bookings' => $formattedBookings,
            'bookings_count' => $bookings->count(),
            'sql_query' => $sql,
            'bindings' => $bindings,
            'all_bookings_count' => \App\Models\Booking::count()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString())
        ], 500);
    }
});



// Room Availability Visualizer
Route::get('/room-visualizer', [App\Http\Controllers\RoomAvailabilityVisualizerController::class, 'index'])
    ->name('room.visualizer');
Route::get('/room-visualizer/data', [App\Http\Controllers\RoomAvailabilityVisualizerController::class, 'getAvailabilityData'])
    ->name('room.visualizer.data');
// Add this route to your web.php file for testing

Route::get('/test-room-visualizer', function() {
    try {
        $controller = new \App\Http\Controllers\RoomAvailabilityVisualizerController();
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'start_date' => \Carbon\Carbon::today()->format('Y-m-d'),
            'end_date' => \Carbon\Carbon::today()->addDays(7)->format('Y-m-d')
        ]);
        
        return $controller->getAvailabilityData($request);
    } catch (\Exception $e) {
        \Log::error('Test route error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString())
        ], 500);
    }
});

// Damage Items Routes
Route::get('/damage-items', [DamageItemController::class, 'index'])->name('damage-items.index');
Route::post('/damage-items', [DamageItemController::class, 'store'])->name('damage-items.store');
Route::get('/damage-items/monthly-report', [DamageItemController::class, 'monthlyReport'])->name('damage-items.monthly-report');

// Room Availability Management Routes
Route::get('/rooms/availability', [RoomAvailabilityController::class, 'index'])->name('rooms.availability');
Route::post('/rooms/store', [RoomAvailabilityController::class, 'storeRoom'])->name('rooms.store');
Route::post('/rooms/checklist-items', [RoomAvailabilityController::class, 'storeChecklistItem'])->name('rooms.checklist.store');
Route::post('/rooms/{room}/daily-check', [RoomAvailabilityController::class, 'dailyCheck'])->name('rooms.daily-check');
Route::post('/rooms/{room}/toggle-booking', [RoomAvailabilityController::class, 'toggleBooking'])->name('rooms.toggle-booking');
Route::post('/rooms/{room}/update-checklist', [RoomAvailabilityController::class, 'updateChecklist'])
    ->name('rooms.update-checklist');

    Route::post('/rooms/{room}/guest-in', [RoomAvailabilityController::class, 'guestIn'])->name('rooms.guest-in');
    Route::post('/rooms/{room}/guest-out', [RoomAvailabilityController::class, 'guestOut'])->name('rooms.guest-out');
    

Route::middleware(['auth', 'VerifyAdmin'])->group(function(){

 
     
     // routes for management
   
     Route::resource('management/user',App\Http\Controllers\Management\UserController::class);

     //route for report
     
     Route::get('/report', 'App\Http\Controllers\Report\ReportController@index')->name('report');
     
     
     Route::get('/report/show', 'App\Http\Controllers\Report\ReportController@show');
     
  
// routes for inventory
     Route::post('inventory/storestock/{itemid}', 'App\Http\Controllers\Inventory\StockController@store')->name('Stock.storeStock');
     Route::delete('inventory/removeStock/{itemid}', 'App\Http\Controllers\Inventory\StockController@destroy')->name('Stock.removeStock');


     // Export to excel
     Route::get('/report/show/export', 'App\Http\Controllers\Report\ReportController@export');
     
    
//salary
Route::middleware(['auth'])->group(function () {
   Route::get('/salary', [SalaryController::class, 'index'])->name('salary.index');
   Route::post('/salary/calculate', [SalaryController::class, 'calculate'])->name('salary.calculate');
   Route::get('/salary/{salary}/payslip', [SalaryController::class, 'payslip'])->name('salary.payslip');
   Route::get('/salary/generate-payslip', [SalaryController::class, 'generatePayslip'])
    ->name('salary.generatePayslip');
    Route::get('/salary/{id}/payslip', [SalaryController::class, 'generatePayslip'])->name('salary.payslip');
   Route::get('/salary/basic', [SalaryController::class, 'basicSalary'])->name('salary.basic');
   Route::post('/salary/update-basic', [SalaryController::class, 'updateBasic'])->name('salary.updateBasic');
   Route::post('/person/update-basic-salary', [SalaryController::class, 'updateBasicSalary'])->name('person.updateBasicSalary');
});


//S/C
Route::middleware(['auth'])->group(function () {
    // Main index page for service charge
    Route::get('/service-charge', [ServiceChargeController::class, 'index'])
        ->name('service-charge.index');
 
    // Update points for an employee
    Route::post('/service-charge/update-points', [ServiceChargeController::class, 'updatePoints'])
        ->name('service-charge.updatePoints');
 
    // Generate service charge for a specific employee
    Route::post('/service-charge/generate', [ServiceChargeController::class, 'generateServiceCharge'])
        ->name('service-charge.generate');
 
    // Print service charge receipt for a specific service charge record
    Route::get('/service-charge/{id}/print', [ServiceChargeController::class, 'printServiceCharge'])
        ->name('service-charge.print');
 
        Route::get('/service-charge/points', [ServiceChargeController::class, 'managePoints'])
     ->name('service-charge.points');
 Route::post('/service-charge/points/update-bulk', [ServiceChargeController::class, 'updatePointsBulk'])
     ->name('service-charge.points.update-bulk');
 });


// Cashier Balance Routes
Route::prefix('cashier')->name('cashier.')->group(function () {
    Route::get('/balance', [CashierBalanceController::class, 'index'])->name('balance');
    Route::post('/balance/update-opening', [CashierBalanceController::class, 'updateOpeningBalance'])->name('update-opening-balance');
    Route::post('/balance/add-transaction', [CashierBalanceController::class, 'addManualTransaction'])->name('add-manual-transaction');
    Route::post('/balance/close-day', [CashierBalanceController::class, 'closeDay'])->name('close-day');
    Route::get('/balance/report', [CashierBalanceController::class, 'generateReport'])->name('report');
});


//summey page
Route::middleware(['auth'])->group(function () {
    Route::get('/sales/summary', [SalesSummaryController::class, 'index'])->name('sales.summary');
    Route::get('/sales/summary/data', [SalesSummaryController::class, 'getSummaryData'])->name('sales.summary.data');
    Route::get('/sales/summary/print', [SalesSummaryController::class, 'printSummary'])->name('sales.summary.print');
});

    });
