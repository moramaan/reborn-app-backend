<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\Auth0Middleware;
use App\Http\Middleware\VerifyJsonContentType;
use App\Http\Middleware\VerifyMultipartContentType;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

// Handle requests to non-existent routes
Route::fallback(function () {
    return response()->json(['error' => 'Endpoint not found'], 404);
});

// User routes
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
// Route::delete('/users/{id}', [UserController::class, 'destroy']);

// Item routes
Route::get('/items', [ItemController::class, 'index']);
Route::get('/items/{id}', [ItemController::class, 'show']);
Route::post('/items/search', [ItemController::class, 'search']);
// Route::delete('/items/{id}', [ItemController::class, 'destroy']);
// Items update and store only accept multipart due to the image upload
// Route::middleware([VerifyMultipartContentType::class])->group(function () {
// Route::put('/items/{id}', [ItemController::class, 'update']);
// Route::post('/items', [ItemController::class, 'store']);
// });

// Transaction routes
Route::get('/transactions', [TransactionController::class, 'index']);
Route::get('/transactions/{id}', [TransactionController::class, 'show']);
Route::delete('/transactions/{id}', function () {
    return response()->json(['error' => 'Method not allowed'], 405);
});
Route::put('/transactions/{id}', function () {
    return response()->json(['error' => 'Method not allowed'], 405);
});

// Create and update routes
// Route::middleware([VerifyJsonContentType::class])->group(function () {
//     Route::post('/users', [UserController::class, 'store']);
//     Route::post('/transactions', [TransactionController::class, 'store']);
//     Route::put('/users/{id}', [UserController::class, 'update']);
// });

Route::middleware([Auth0Middleware::class])->group(function () {
    Route::put('/items/{id}', [ItemController::class, 'update']);
    Route::delete('/items/{id}', [ItemController::class, 'destroy']);
    Route::post('/items', [ItemController::class, 'store']);
    Route::delete('/items/{id}', [ItemController::class, 'destroy']);
});


Route::middleware([Auth0Middleware::class, VerifyJsonContentType::class])->group(function () {
    Route::post('/users', [UserController::class, 'store']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});

// Route::get('decode-token', [AuthController::class, 'decodeToken']);
// Auth0 token decoding route