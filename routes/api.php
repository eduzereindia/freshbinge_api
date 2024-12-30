<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AddressController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\ServiceLocationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

// Category routes
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get('/categories/{category}/subcategories', [CategoryController::class, 'getSubcategories']);

// Product routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/products/{product}/variants', [ProductController::class, 'getVariants']);

// Service Location routes
Route::get('/service-locations', [ServiceLocationController::class, 'index']);
Route::post('/check-serviceability', [ServiceLocationController::class, 'checkServiceability']);
Route::get('/service-locations/{serviceLocation}/delivery-slots', [ServiceLocationController::class, 'getDeliverySlots']);

// Cart routes
Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart/add', [CartController::class, 'addItem']);
Route::put('/cart/items/{cartItem}', [CartController::class, 'updateItem']);
Route::delete('/cart/items/{cartItem}', [CartController::class, 'removeItem']);
Route::delete('/cart/clear', [CartController::class, 'clear']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::post('/change-password', [UserController::class, 'changePassword']);
    Route::get('/orders', [UserController::class, 'orders']);

    // Address routes
    Route::apiResource('addresses', AddressController::class);
    Route::post('addresses/{address}/set-default', [AddressController::class, 'setDefault']);

    // Admin routes
    Route::middleware('admin')->group(function () {
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);
        Route::apiResource('service-locations', ServiceLocationController::class)->except(['index']);
    });

    // Cart merge route
    Route::post('/cart/merge', [CartController::class, 'mergeGuestCart']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
