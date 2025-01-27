<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    // Add a review for a product
    public function addReview(Request $request, $productId)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|between:1,5', // Rating must be between 1 and 5
            'comment' => 'nullable|string|max:500', // Optional comment
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if the product exists
        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Create the review
        $review = Review::create([
            'product_id' => $productId,
            'user_id' => auth()->id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json(['message' => 'Review added successfully', 'review' => $review], 201);
    }

    // Update a review
    public function updateReview(Request $request, $reviewId)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|between:1,5', // Rating must be between 1 and 5
            'comment' => 'nullable|string|max:500', // Optional comment
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find the review by ID
        $review = Review::where('id', $reviewId)
                        ->where('user_id', auth()->id()) // Ensure the review belongs to the authenticated user
                        ->first();

        if (!$review) {
            return response()->json(['message' => 'Review not found or unauthorized'], 404);
        }

        // Update the review
        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json(['message' => 'Review updated successfully', 'review' => $review], 200);
    }

    // Delete a review
    public function deleteReview($reviewId)
    {
        // Find the review by ID
        $review = Review::where('id', $reviewId)
                        ->where('user_id', auth()->id()) // Ensure the review belongs to the authenticated user
                        ->first();

        if (!$review) {
            return response()->json(['message' => 'Review not found or unauthorized'], 404);
        }

        // Delete the review
        $review->delete();

        return response()->json(['message' => 'Review deleted successfully'], 200);
    }

    // Get all reviews for a specific product
    public function getProductReviews($productId)
    {
        // Find the product by ID
        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Get all reviews for the product
        $reviews = $product->reviews()->with('user')->get(); // Include user information with each review

        return response()->json(['reviews' => $reviews], 200);
    }
}
