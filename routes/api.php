<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CampaignController;
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
Route::controller(ProductController::class)->group(function () {
    Route::post('/productAdd', 'productAdd');
});
Route::controller(OrderController::class)->group(function () {
    Route::post('/createOrder', 'createOrder');
});
Route::controller(OrderController::class)->group(function () {
    Route::post('/orderDetail/{orderNumber}', 'orderDetail');
});
