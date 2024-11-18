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
     
      // routes for petty cash

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

// routes for calender
Route::get('/calendar', function () {
   return view('calendar');
})->name('calendar');
Route::get('/bookings', [BookingController::class, 'index']);
Route::post('/bookings', [BookingController::class, 'store']);
Route::put('/bookings/{id}', [BookingController::class, 'update']);
Route::get('/available-rooms', [BookingController::class, 'availableRooms']);



// routes for inventory
Route::get('/stock', [InventoryController::class, 'index'])->name('stock.index');
Route::post('/stock', [InventoryController::class, 'store'])->name('stock.store');
Route::post('/categories', [InventoryController::class, 'storeCategory'])->name('categories.store');
Route::post('/items', [InventoryController::class, 'storeItem'])->name('items.store');
Route::post('/stock/monthly', [InventoryController::class, 'viewMonthlyStock'])->name('stock.monthly');
Route::post('/stock/update', [InventoryController::class, 'updateTodayStock'])->name('stock.update');
Route::get('/stock/test-propagation', [InventoryController::class, 'checkStockPropagation'])->name('stock.test-propagation');

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

// Expenses
Route::resource('groups', GroupController::class);
Route::resource('costs', CostController::class);
Route::resource('persons', PersonController::class);

// Task
// Home Route
Route::get('/', function () {
   return redirect()->route('tasks.index');
});
// Task Routes
Route::resource('tasks', TaskController::class);
Route::post('tasks/{id}/update-status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
// Task Category Routes
Route::resource('task-categories', TaskCategoryController::class);
Route::get('completed-tasks', [CompletedTaskController::class, 'index'])->name('completed-tasks.index');





Route::middleware(['auth', 'VerifyAdmin'])->group(function(){

 
     
     
     // routes for management
   
     Route::resource('management/user',App\Http\Controllers\Management\UserController::class);

     //route for report
     
     Route::get('/report', 'App\Http\Controllers\Report\ReportController@index')->name('report');
     
     
     Route::get('/report/show', 'App\Http\Controllers\Report\ReportController@show');
     
     // cashier
   
     // routes for inventory
     Route::post('inventory/storestock/{itemid}', 'App\Http\Controllers\Inventory\StockController@store')->name('Stock.storeStock');
     Route::delete('inventory/removeStock/{itemid}', 'App\Http\Controllers\Inventory\StockController@destroy')->name('Stock.removeStock');


     // Export to excel
     Route::get('/report/show/export', 'App\Http\Controllers\Report\ReportController@export');
     
    



    });
