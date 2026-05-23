<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderFileController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\OrderMessageController;
use App\Http\Controllers\Api\TestimonialController;

Route::get('/health', function () {
    return response()->json([
        'status' => true,
        'message' => 'Submit Mate BD API is running',
    ]);
});

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{id}', [ServiceController::class, 'show']);
Route::get('/packages', [PackageController::class, 'index']);
Route::get('/packages/{id}', [PackageController::class, 'show']);
Route::get('/services/{serviceId}/packages', [PackageController::class, 'packagesByService']);
Route::get('/testimonials', [TestimonialController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Protected Student + Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Student order routes
    Route::post('/orders', [OrderController::class, 'createOrder']);
    Route::get('/my-orders', [OrderController::class, 'myOrders']);
    Route::get('/orders/{id}', [OrderController::class, 'showOrder']);

    // Order file routes
    Route::post('/orders/{orderId}/upload-file', [OrderFileController::class, 'uploadStudentFile']);
    Route::get('/orders/{orderId}/files', [OrderFileController::class, 'orderFiles']);
    Route::get('/order-files/{fileId}/download', [OrderFileController::class, 'downloadFile']);

    // Manual payment routes
    Route::post('/orders/{orderId}/payments', [PaymentController::class, 'submitManualPayment']);
    Route::get('/orders/{orderId}/payments', [PaymentController::class, 'orderPayments']);

    // Order message routes
    Route::get('/orders/{orderId}/messages', [OrderMessageController::class, 'index']);
    Route::post('/orders/{orderId}/messages', [OrderMessageController::class, 'store']);

    // Admin order routes
    Route::get('/admin/orders', [OrderController::class, 'allOrders']);
    Route::patch('/admin/orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::post('/admin/orders/{orderId}/upload-final-file', [OrderFileController::class, 'uploadFinalFile']);
    Route::patch('/admin/payments/{paymentId}/verify', [PaymentController::class, 'verifyPayment']);

    // Admin service/package management
    Route::get('/admin/services', [ServiceController::class, 'adminIndex']);
    Route::post('/admin/services', [ServiceController::class, 'store']);
    Route::put('/admin/services/{id}', [ServiceController::class, 'update']);
    Route::delete('/admin/services/{id}', [ServiceController::class, 'destroy']);

    Route::get('/admin/packages', [PackageController::class, 'adminIndex']);
    Route::post('/admin/packages', [PackageController::class, 'store']);
    Route::put('/admin/packages/{id}', [PackageController::class, 'update']);
    Route::delete('/admin/packages/{id}', [PackageController::class, 'destroy']);

    // Admin testimonials
    Route::get('/admin/testimonials', [TestimonialController::class, 'adminIndex']);
    Route::post('/admin/testimonials', [TestimonialController::class, 'store']);
    Route::put('/admin/testimonials/{id}', [TestimonialController::class, 'update']);
    Route::delete('/admin/testimonials/{id}', [TestimonialController::class, 'destroy']);
});
