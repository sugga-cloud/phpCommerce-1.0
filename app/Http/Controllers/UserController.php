<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Update the authenticated user's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . auth()->user()->id, // Ensure email is unique, excluding current user's email
            'password' => 'nullable|string|min:8', // Password must be confirmed if provided
            'role' => 'nullable|string|in:admin,customer,user', // You may want to limit the roles
            'address' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|min:10',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Optional profile picture upload
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get the authenticated user
        $user = auth()->user();

        // Update user information
        if ($request->has('first_name')) {
            $user->first_name = $request->first_name;
        }

        if ($request->has('last_name')) {
            $user->last_name = $request->last_name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('role')) {
            $user->role = $request->role;
        }

        if ($request->has('address')) {
            $user->address = $request->address;
        }

        if ($request->has('phone_number')) {
            $user->phone_number = $request->phone_number;
        }

        // Handle password update
        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        // Handle profile picture update (if provided)
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture from storage
            if ($user->profile_picture && Storage::exists('public/' . $user->profile_picture)) {
                Storage::delete('public/' . $user->profile_picture);
            }

            // Store the new profile picture
            $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
            $user->profile_picture = $profilePicturePath;
        }

        // Save the updated user information to the database
        $user->save();

        return response()->json(['message' => 'User profile updated successfully.', 'user' => $user]);
    }

    public function delete(Request $request)
    {
        // Get the authenticated user
        $user = auth()->user();

        // Handle profile picture deletion
        if ($user->profile_picture && Storage::exists('public/' . $user->profile_picture)) {
            Storage::delete('public/' . $user->profile_picture);
        }

        // Optionally, you may want to delete the user's related records, such as orders, reviews, etc.
        // For example, you can delete orders, reviews, and other related data:
        // Delete the user's orders and reviews if any exist
if ($user->orders()->exists()) {
    $user->orders()->delete(); // Delete orders related to the user
}

if ($user->reviews()->exists()) {
    $user->reviews()->delete(); // Delete reviews related to the user
}

        // Delete the user's account
        $user->delete();

        // Return a response indicating the account has been removed
        return response()->json(['message' => 'Your account has been deleted successfully.'], 200);
    }
}

