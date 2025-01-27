<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    // Add product to the cart
    public function addToCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id', // Ensure product exists
            'quantity' => 'required|integer|min:1', // Ensure valid quantity
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if product already exists in the user's cart
        $cartItem = Cart::where('user_id', auth()->id())
                        ->where('product_id', $request->product_id)
                        ->first();

        if ($cartItem) {
            // If item exists, update the quantity
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
            return response()->json(['message' => 'Cart updated successfully', 'cart' => $cartItem], 200);
        } else {
            // If item does not exist in cart, create a new cart item
            $cartItem = Cart::create([
                'user_id' => auth()->id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ]);

            return response()->json(['message' => 'Product added to cart', 'cart' => $cartItem], 201);
        }
    }

    // Update the quantity of a product in the cart
    public function updateCart(Request $request, $cartId)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1', // Ensure valid quantity
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find the cart item by its ID
        $cartItem = Cart::where('id', $cartId)
                        ->where('user_id', auth()->id())
                        ->first();

        if ($cartItem) {
            // Update the quantity
            $cartItem->quantity = $request->quantity;
            $cartItem->save();
            return response()->json(['message' => 'Cart item updated successfully', 'cart' => $cartItem], 200);
        } else {
            return response()->json(['message' => 'Cart item not found'], 404);
        }
    }

    // Remove a product from the cart
    public function removeFromCart($cartId)
    {
        // Find the cart item by its ID
        $cartItem = Cart::where('id', $cartId)
                        ->where('user_id', auth()->id())
                        ->first();

        if ($cartItem) {
            // Delete the cart item
            $cartItem->delete();
            return response()->json(['message' => 'Product removed from cart'], 200);
        } else {
            return response()->json(['message' => 'Cart item not found'], 404);
        }
    }

    // Get all items in the user's cart
    public function getCart()
    {
        // Get all cart items for the authenticated user
        $cartItems = Cart::where('user_id', auth()->id())
                         ->with('product') // Load product data for each cart item
                         ->get();

        return response()->json($cartItems);
    }
}
