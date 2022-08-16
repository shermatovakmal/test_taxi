<?php

use App\Http\Controllers\DriversController;
use App\Http\Controllers\OrdersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PassportController;


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
/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/
Route::post('register', [PassportController::class, 'register']);
Route::post('login', [PassportController::class, 'login']);

// put all api protected routes here
Route::middleware('auth:api')->group(function () {
    Route::post('user-detail', [PassportController::class, 'userDetail']);
    Route::post('logout', [PassportController::class, 'logout']);

    Route::post('order', [OrdersController::class, 'createOrderJson']);
    Route::patch('order-assign2driver', [OrdersController::class, 'assignDriverJson']);
    Route::post('driver', [DriversController::class, 'createDriverJson']);
});
