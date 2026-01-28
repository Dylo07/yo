<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AvailabilityController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Public API v1 Routes (Protected by API Key)
|--------------------------------------------------------------------------
|
| These routes are for external integrations (e.g., sobalanka.com)
| All routes require a valid API key via X-API-KEY header or api_key query param
|
*/

Route::prefix('v1')->middleware('api.key')->group(function () {
    // Room availability endpoint for public website sync
    Route::get('/availability', [AvailabilityController::class, 'index']);
    
    // Health check endpoint
    Route::get('/health', [AvailabilityController::class, 'health']);
});
