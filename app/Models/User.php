<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\User as Authenticatable; // Add this

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 
        'last_name', 
        'email', 
        'password', 
        'role', 
        'address', 
        'phone_number', 
        'profile_picture', // For user's profile picture, if required
        'email_verified_at' // To manage email verification
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    // Relationship with Orders
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Relationship with Reviews
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Relationship with Cart
    public function cart()
    {
        return $this->hasMany(Cart::class);
    }

    // Relationship with Wishlist
    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Boot the model.
     * Hash the password when setting.
     */
    public static function boot()
    {
        parent::boot();

        // Automatically hash the password on creating or updating
        static::creating(function ($user) {
            if ($user->password) {
                $user->password = Hash::make($user->password);
            }
        });

        static::updating(function ($user) {
            if ($user->password && $user->isDirty('password')) {
                $user->password = Hash::make($user->password);
            }
        });
    }

    /**
     * Get the user's role (for authorization).
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    /**
     * Additional utility methods (optional).
     * For example, to easily get user's full address:
     */
    public function getFullAddress()
    {
        return $this->address;
    }

    /**
     * Profile Picture URL (optional).
     */
    public function getProfilePictureUrl()
    {
        return $this->profile_picture ? asset('storage/'.$this->profile_picture) : null;
    }
}
