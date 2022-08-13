<?php

use App\Http\Controllers\OrdersController;
use Illuminate\Support\Facades\Route;

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

Route::get('/orders', [OrdersController::class, 'index'])->name('orders.list');
Route::get('/orders/ongoing', [OrdersController::class, 'ongoingOrders'])->name('orders.ongoing');
Route::get('/orders/delivered', [OrdersController::class, 'deliveredOrders'])->name('orders.delivered');

Route::post('/orders/assign', [OrdersController::class, 'assignDriver'])->name('orders.assignDrv');
Route::post('/orders/deliver', [OrdersController::class, 'deliverOrder'])->name('orders.deliverOrder');
