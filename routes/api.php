<?php

use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\OrderController;

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

Route::middleware('auth:sanctum')->group(function () {
    // Route to get orders for the authenticated user
    Route::get('orders', [OrderController::class, 'index']);
    
    // Route to create a new order with items
    Route::post('orders', [OrderController::class, 'store']);
    
    // Route to get a specific order
    Route::get('orders/{orderId}', [OrderController::class, 'show']);
    
    // Route to update an existing order
    Route::put('orders/{orderId}', [OrderController::class, 'update']);
    
    // Route to delete an order
    Route::delete('orders/{orderId}', [OrderController::class, 'destroy']);
});


Route::middleware('auth:sanctum')->group(function () {
    // Add a review for a product
    Route::post('/products/{productId}/reviews', [ReviewController::class, 'addReview']);

    // Update a review
    Route::put('/reviews/{reviewId}', [ReviewController::class, 'updateReview']);

    // Delete a review
    Route::delete('/reviews/{reviewId}', [ReviewController::class, 'deleteReview']);
});

Route::get('/products/{productId}/reviews', [ReviewController::class, 'getProductReviews']);

Route::middleware('auth:sanctum')->group(function () {
    // Get all items in the user's cart
    Route::get('/cart', [CartController::class, 'getCart']);

    // Add a product to the cart
    Route::post('/cart', [CartController::class, 'addToCart']);

    // Update the quantity of a product in the cart
    Route::put('/cart/{cartId}', [CartController::class, 'updateCart']);

    // Remove a product from the cart
    Route::delete('/cart/{cartId}', [CartController::class, 'removeFromCart']);
});

Route::get('/products', [ProductController::class, 'index']);

Route::get('/products/{id}', [ProductController::class, 'show']);
Route::prefix("admin")->group(function () {
    // Get all products

    // Get a specific product by ID

    // Create a new product
    Route::post('/products', [ProductController::class, 'store']);

    // Update a product
    Route::post('/products/{id}', [ProductController::class, 'update']);

    // Delete a product
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth:sanctum')->put('/user/update', [UserController::class, 'update']);

Route::middleware('auth:sanctum')->delete('/user/delete', [UserController::class, 'delete']);

Route::get('/', function (Request $request){ return 'Hello World'; });
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
