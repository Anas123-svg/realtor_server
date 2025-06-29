<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HeroSectionFeaturedProperty;
use App\Models\Property;

class HeroSectionFeaturedPropertyController extends Controller
{
    public function add($id)
    {
        $featuredCount = HeroSectionFeaturedProperty::count();
        if ($featuredCount >= 12) {
            return response()->json(['message' => 'Cannot add more than 12 properties to hero section.'], 400);
        }
    
        $property = Property::find($id);
        if (!$property) {
            return response()->json(['message' => 'Property not found.'], 404);
        }
    
        $exists = HeroSectionFeaturedProperty::where('property_id', $id)->exists();
        if ($exists) {
            return response()->json(['message' => 'Property already featured.'], 409);
        }
    
        HeroSectionFeaturedProperty::create(['property_id' => $id]);
    
        return response()->json(['message' => 'Property added to hero section.'], 201);
    }
    

    public function remove($id)
    {
        $featured = HeroSectionFeaturedProperty::where('property_id', $id)->first();

        if (!$featured) {
            return response()->json(['message' => 'Property not found in hero section.'], 404);
        }

        $featured->delete();

        return response()->json(['message' => 'Property removed from hero section.'], 200);
    }

    public function index()
    {
        $featuredProperties = HeroSectionFeaturedProperty::with('property')->get();
        return response()->json($featuredProperties);
    }

    public function heroAndNonHeroProperties()
{
    $featuredPropertyIds = HeroSectionFeaturedProperty::pluck('property_id')->toArray();

    $heroProperties = Property::with(['images', 'location', 'admin'])
        ->whereIn('id', $featuredPropertyIds)
        ->get();

    $nonHeroProperties = Property::with(['images', 'location', 'admin'])
        ->whereNotIn('id', $featuredPropertyIds)
        ->get();

    return response()->json([
        'heroProperties' => $heroProperties,
        'nonHeroProperties' => $nonHeroProperties,
    ]);
}

}
