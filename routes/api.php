<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Login Required)
|--------------------------------------------------------------------------
*/
// Only put routes here that DO NOT need specific store/user data
// e.g., Login, Register, Public Catalog (if not store-specific)
Route::get('/public-info', function() {
    return response()->json(['status' => 'ok']);
});


/*
|--------------------------------------------------------------------------
| Protected Routes (Login Required)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    
    // FETCH: Get products (Now safe because user is logged in)
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/fetch', [ProductController::class, 'fetch']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    // WRITE: Create, Update, Delete
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});