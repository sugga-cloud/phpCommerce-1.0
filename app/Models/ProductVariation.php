<?php
// app/Models/ProductVariation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'size', 'color', 'price', 'stock_quantity'];

    // Relationship with Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
