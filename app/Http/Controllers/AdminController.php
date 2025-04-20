<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use App\Models\Property;
use App\Models\SiteViewr;


class AdminController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|min:6',
            'phone' => 'required|string',
            'whatsapp' => 'nullable|string',
        ]);

        $admin = Admin::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
            'whatsapp' => $validated['whatsapp'],
            'profile_image' => $request->profile_image ?? null,
        ]);

        return response()->json(['message' => 'Admin registered successfully.', 'admin' => $admin]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        $admin = Admin::where('email', $credentials['email'])->first();
    
        if (!$admin || !Hash::check($credentials['password'], $admin->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    
        $token = $admin->createToken('admin-token')->plainTextToken;
    
        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'admin' =>$admin
        ]);
    }
    

    public function getAdminByToken(Request $request)
    {
        return response()->json(['admin' => $request->user()]);
    }

    public function getAdmins()
    {
        $admins = Admin::all();
        return response()->json(['admins' => $admins]);
    }

    public function getAdmin($id)
    {
        $admin = Admin::findOrFail($id);
        return response()->json(['admin' => $admin]);
    }

    public function updateAdmin(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $validated = $request->validate([
            'username' => 'nullable|string',
            'email' => 'nullable|email|unique:admins,email,' . $id,
            'password' => 'nullable|min:6',
            'phone' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'profile_image' => 'nullable|string',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $admin->update($validated);

        return response()->json(['message' => 'Admin updated successfully.', 'admin' => $admin]);
    }

    public function deleteAdmin($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->delete();

        return response()->json(['message' => 'Admin deleted successfully.']);
    }


    public function updatePassword(Request $request)
{
    $request->validate([
        'old_password' => 'required',
        'new_password' => 'required|min:8|confirmed', 
    ]);

    $admin = auth()->user(); 

    if (!Hash::check($request->old_password, $admin->password)) {
        return response()->json(['message' => 'Old password is incorrect'], 400);
    }

    $admin->password = Hash::make($request->new_password);
    $admin->save();

    return response()->json(['message' => 'Password updated successfully']);
}
///admin panel apis


public function dashboard()
{
    $properties = Property::with(['images', 'location'])->get();

    $properties = $properties->map(function ($property) {
        return $this->transformPropertyResponse($property);
    });
    $siteViews = SiteViewr::first()->SiteViews ?? 0;


    return response()->json([
        'siteViews' => $siteViews,
        'properties' => $properties,
    ]);
}

function transformPropertyResponse($property)
{
    return [
        "id" => $property['id'],
        "title" => $property['title'],
        "description" => $property['description'],
        "bedrooms" => $property['bedrooms'],
        "bathrooms" => $property['bathrooms'],
        "area" => $property['area'],
        "leaseTerm" => $property['leaseTerm'],
        "propertyType" => $property['propertyType'],
        "category" => $property['category'],
        "dealType" => $property['dealType'],
        "floors" => $property['floors'],
        "noiseLevel" => $property['noiseLevel'],
        "laundry" => $property['laundry'],
        "internet" => $property['internet'],
        "condition" => $property['condition'],
        "video" => $property['video'],
        "price" => (float) $property['price'],
        "view" => is_array($property['view']) ? $property['view'] : [$property['view']],
        "outdoor" => is_array($property['outdoor']) ? $property['outdoor'] : [$property['outdoor']],
        "propertyStyle" => is_array($property['propertyStyle']) ? $property['propertyStyle'] : [$property['propertyStyle']],
        "securityFeatures" => is_array($property['securityFeatures']) ? $property['securityFeatures'] : [$property['securityFeatures']],
        "amenities" => is_array($property['amenities']) ? $property['amenities'] : [$property['amenities']],
        "heating" => is_array($property['heating']) ? $property['heating'] : [$property['heating']],
        "cooling" => is_array($property['cooling']) ? $property['cooling'] : [$property['cooling']],
        "priceType" => $property['priceType'],
        "views" => (int) $property['views'],
        "likes" => (int) $property['likes'],
        "created_at" => $property['created_at'],
        "updated_at" => $property['updated_at'],
        "images" => $property['images']->map(fn($image) => $image['image'])->toArray(),
        "location" => $property['location'] ? [
            "id" => $property['location']['id'],
            "property_id" => $property['location']['property_id'],
            "longitude" => $property['location']['longitude'],
            "latitude" => $property['location']['latitude'],
            "region" => $property['location']['region'],
            "created_at" => $property['location']['created_at'],
            "updated_at" => $property['location']['updated_at']
        ] : null,
    ];
}


}
