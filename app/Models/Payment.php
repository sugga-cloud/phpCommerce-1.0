<?php
// app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'amount', 'payment_method', 'status', 'payment_date'];

    // Relationship with Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
