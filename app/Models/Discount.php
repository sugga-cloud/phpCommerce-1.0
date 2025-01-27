<?php
// app/Models/Discount.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'type', 'value', 'expiration_date', 'usage_count'];

    // Relationship with Order (if applicable)
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
