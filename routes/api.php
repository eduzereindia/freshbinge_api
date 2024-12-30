<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\{
    AddressController,
    AuthController,
    CategoryController,
    CartController,
    ProductController,
    ServiceLocationController,
    UserController
};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

/**
 * Authentication Routes
 * These routes handle user registration, login, and authentication
 */
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/register/verify', [AuthController::class, 'verifyRegistrationOtps']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/login/request-otp', [AuthController::class, 'requestLoginOtp']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
});

/**
 * Public Routes
 * These routes do not require authentication to access
 */
Route::group(function () {
    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::get('/categories/{category}/subcategories', [CategoryController::class, 'getSubcategories']);

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/products/{product}/variants', [ProductController::class, 'getVariants']);

    // Service Locations
    Route::get('/service-locations', [ServiceLocationController::class, 'index']);
    Route::post('/check-serviceability', [ServiceLocationController::class, 'checkServiceability']);
    Route::get('/service-locations/{serviceLocation}/delivery-slots', [ServiceLocationController::class, 'getDeliverySlots']);

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'addItem']);
    Route::put('/cart/items/{cartItem}', [CartController::class, 'updateItem']);
    Route::delete('/cart/items/{cartItem}', [CartController::class, 'removeItem']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
});

/**
 * Protected Routes
 * These routes require authentication to access
 */
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // User Profile
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'profile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::post('/change-password', [UserController::class, 'changePassword']);
        Route::get('/orders', [UserController::class, 'orders']);
    });
    
    // Address Management
    Route::prefix('addresses')->group(function () {
        Route::apiResource('', AddressController::class);
        Route::post('/{address}/set-default', [AddressController::class, 'setDefault']);
    });
    
    // Cart merge route
    Route::post('/cart/merge', [CartController::class, 'mergeGuestCart']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
