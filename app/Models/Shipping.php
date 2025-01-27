<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order; // Ensure that the Order class exists in this namespace

class Shipping extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'tracking_number', 'shipping_status', 'shipping_date'];

    // Relationship with Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
