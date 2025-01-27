<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // Store a new product along with variations
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
            'category_id' => 'required|exists:categories,id',
            'variations' => 'array',
            'variations.*.size' => 'string',
            'variations.*.color' => 'string',
            'variations.*.price' => 'numeric',
            'variations.*.stock_quantity' => 'integer',
            'image_url' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // File validation
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
         // Handle the image upload
         $imagePath = null;
         if ($request->hasFile('image_url')) {
             // Store the image in the 'public/products' directory and get the file path
             $imagePath = $request->file('image_url')->store('products', 'public');
         }

        // Create the product
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock_quantity' => $request->stock_quantity,
            'category_id' => $request->category_id,
            'image_url' => $imagePath,
        ]);

        // Create variations for the product
        if($request->variations){
        foreach ($request->variations as $variationData) {
            ProductVariation::create([
                'product_id' => $product->id,
                'size' => $variationData['size'],
                'color' => $variationData['color'],
                'price' => $variationData['price'],
                'stock_quantity' => $variationData['stock_quantity'],
            ]);
        }}

        return response()->json($product, 201);
    }

    // Update an existing product and its variations
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
            'category_id' => 'required|exists:categories,id',
            'variations' => 'nullable|array',
            'variations.*.id' => 'nullable|exists:product_variations,id', // for updating existing variations
            'variations.*.size' => 'required|string',
            'variations.*.color' => 'required|string',
            'variations.*.price' => 'required|numeric',
            'variations.*.stock_quantity' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update product details
        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock_quantity' => $request->stock_quantity,
            'category_id' => $request->category_id,
        ]);

        // Update or create variations for the product
        if ($request->has('variations')) {
            foreach ($request->variations as $variationData) {
                // If an ID is provided for the variation, update the existing one
                if (isset($variationData['id'])) {
                    $variation = ProductVariation::find($variationData['id']);
                    if ($variation) {
                        $variation->update($variationData);
                    }
                } else {
                    // If no ID, create a new variation
                    ProductVariation::create([
                        'product_id' => $product->id,
                        'size' => $variationData['size'],
                        'color' => $variationData['color'],
                        'price' => $variationData['price'],
                        'stock_quantity' => $variationData['stock_quantity'],
                    ]);
                }
            }
        }

        return response()->json($product, 200);
    }

    // Get all products with their variations
    public function index()
    {
        $products = Product::with('variations')->get();
        return response()->json($products);
    }

    // Show a specific product along with variations
    public function show($id)
    {
        $product = Product::with('variations')->findOrFail($id);
        return response()->json($product);
    }

    // Delete a product and its variations
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->variations()->delete();  // Delete associated variations
        $product->delete();  // Delete the product

        return response()->json(['message' => 'Product and variations deleted successfully'], 200);
    }
}
