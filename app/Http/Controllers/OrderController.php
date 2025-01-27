<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders for the authenticated user.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();  // Get authenticated user
        $orders = $user->orders; // Get all orders for this user
        
        return response()->json($orders);
    }

    /**
     * Store a newly created order along with its order items in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate incoming data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'order_items' => 'required|array',
            'order_items.*.product_id' => 'required|exists:products,id',
            'order_items.*.quantity' => 'required|integer|min:1',
            'order_items.*.price' => 'required|numeric',
        ]);

        // Start a database transaction
        \DB::beginTransaction();

        try {
            // Create an order
            $order = new Order();
            $order->user_id = Auth::id(); // Set the authenticated user's ID
            $order->name = $validatedData['name'];
            $order->description = $validatedData['description'];
            $order->save(); // Save the order

            // Loop through each order item and add it to the order
            foreach ($validatedData['order_items'] as $item) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id; // Associate with this order
                $orderItem->product_id = $item['product_id']; // Set product
                $orderItem->quantity = $item['quantity']; // Set quantity
                $orderItem->price = $item['price']; // Set price
                $orderItem->save(); // Save the order item
            }

            // Commit the transaction
            \DB::commit();

            return response()->json([
                'message' => 'Order and order items created successfully',
                'order' => $order
            ], 201);
        } catch (\Exception $e) {
            // Rollback transaction if an error occurs
            \DB::rollBack();
            return response()->json(['message' => 'Error creating order'], 500);
        }
    }

    /**
     * Display the specified order with its items.
     *
     * @param  int  $orderId
     * @return \Illuminate\Http\Response
     */
    public function show($orderId)
    {
        // Get the order along with order items
        $order = Order::with('orderItems.product')->find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        return response()->json($order);
    }

    /**
     * Update the specified order (e.g., changing the status, etc.).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $orderId
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $orderId)
    {
        // Validate the data
        $validatedData = $request->validate([
            'name' => 'string|max:255',
            'description' => 'string',
            // You can add other fields if necessary
        ]);

        // Find the order
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        // Update the order
        $order->update($validatedData);

        return response()->json(['message' => 'Order updated successfully', 'order' => $order]);
    }

    /**
     * Remove the specified order and its order items.
     *
     * @param  int  $orderId
     * @return \Illuminate\Http\Response
     */
    public function destroy($orderId)
    {
        // Find the order
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        // Delete associated order items first
        $order->orderItems()->delete();

        // Now delete the order
        $order->delete();

        return response()->json(['message' => 'Order and its items deleted successfully']);
    }
}
