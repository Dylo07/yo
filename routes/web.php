<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\CategoryController;
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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/management',function(){
   return view('management.index'); 
})->name('management');
//routes for cashier
Route::get('/cashier', 'App\Http\Controllers\Cashier\CashierController@index')->name('cashier');
Route::get('/cashier/getMenuByCategory/{category_id}', 'App\Http\Controllers\Cashier\CashierController@getMenuByCategory');


Route::get('/cashier/getTable', 'App\Http\Controllers\Cashier\CashierController@getTables');
Route::get('/cashier/getSaleDetailsByTable/{table_id}', 'App\Http\Controllers\Cashier\CashierController@getSaleDetailsByTable');


Route::post('/cashier/orderFood', 'App\Http\Controllers\Cashier\CashierController@orderFood');

Route::post('/cashier/deleteSaleDetail', 'App\Http\Controllers\Cashier\CashierController@deleteSaleDetail');
Route::post('/cashier/increase-quantity', 'App\Http\Controllers\Cashier\CashierController@increaseQuantity');

Route::post('/cashier/decrease-quantity', 'App\Http\Controllers\Cashier\CashierController@decreaseQuantity');

Route::post('/cashier/confirmOrderStatus','App\Http\Controllers\Cashier\CashierController@confirmOrderStatus' );
Route::post('/cashier/savePayment', 'App\Http\Controllers\Cashier\CashierController@savePayment');

Route::get('/cashier/showRecipt/{saleID}', 'App\Http\Controllers\Cashier\CashierController@showRecipt');





// routes for management
Route::resource('management/category', App\Http\Controllers\Management\CategoryController::class);
Route::resource('management/menu', App\Http\Controllers\Management\MenuController::class);
Route::resource('management/table', App\Http\Controllers\Management\TableController::class);
Route::resource('management/user',App\Http\Controllers\Management\UserController::class);
//route for report

Route::get('/report', 'App\Http\Controllers\Report\ReportController@index')->name('report');


Route::get('/report/show', 'App\Http\Controllers\Report\ReportController@show');



// Export to excel
Route::get('/report/show/export', 'App\Http\Controllers\Report\ReportController@export');

// route for inventory
Route::get('/inventory',function(){
    return view('inventory.index'); 
 })->name('inventory');

 // routes for inventory
Route::resource('inventory/category', App\Http\Controllers\Inventory\CategoryController::class);
Route::resource('inventory/menu', App\Http\Controllers\Inventory\MenuController::class);
Route::resource('inventory/stock', App\Http\Controllers\Inventory\StockController::class);
Route::resource('inventory/table', App\Http\Controllers\Inventory\TableController::class);