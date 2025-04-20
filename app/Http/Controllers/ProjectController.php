<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class ProjectController extends Controller
{
public function index()
{
    $projects = Project::all()->map(function ($project) {
        return [
            'id' => $project->id,
            'title' => $project->title,
            'description' => $project->description,
            'projectType' => $project->projectType,
            'price' => (int) $project->price, // Ensuring price is an integer
            'images' => $project->images,
            'videos' => $project->videos,
            'address' => $project->address,
            'longitude' => $project->longitude,
            'latitude' => $project->latitude,
            'region' => $project->region,
            'developerInformation' => $project->developerInformation,
            'neighborhood' => $project->neighborhood,
            'communityFeatures' => $project->communityFeatures,
            'sustainabilityFeatures' => $project->sustainabilityFeatures,
            'investmentReason' => $project->investmentReason,
            'amenities' => $project->amenities,
            'progress' => $project->progress,
            'investmentPotential' => $project->investmentPotential,
            'FAQ' => $project->FAQ,
            'delivery_time' => $project->delivery_time,
        ];
    });

    return response()->json($projects, 200);
}


    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'images' => 'required|array',
            'projectType' => 'required|string',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'videos' => 'nullable|array',
            'address' => 'required|string',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'region' => 'required|string',
            'developerInformation' => 'required|string',
            'neighborhood' => 'nullable|array',
            'communityFeatures' => 'nullable|array',
            'sustainabilityFeatures' => 'nullable|array',
            'investmentReason' => 'nullable|array',
            'amenities' => 'nullable|array',
            'progress' => 'required|integer',
            'delivery_time' => 'nullable|string',
            'investmentPotential' => 'required|string',
            'FAQ' => 'nullable|array',
            'properties' => 'nullable|array'
        ]);

        $project = Project::create($data);
        return response()->json($project, 201);
    }

public function show($id)
{
    $project = Project::findOrFail($id);
    
    if (isset($project->properties) && count($project->properties) > 0) {
        $propertyIds = $project->properties;

        $properties = Property::with(['images', 'location']) // Load location data
            ->whereIn('id', $propertyIds)
            ->get();

        $minPrice = (int) $properties->min('price');
        $maxPrice = (int) $properties->max('price');
        $priceRange = "$minPrice - $maxPrice";

        $properties = $properties->map(function ($property) {
            return [
                'id' => $property->id,
                'title' => $property->title,
                'description' => $property->description,
                'bedrooms' => $property->bedrooms,
                'bathrooms' => $property->bathrooms,
                'area' => $property->area,
                'propertyType' => $property->propertyType,
                'category' => $property->category,
                'dealType' => $property->dealType,
                'leaseTerm' => $property->leaseTerm,
                'floors' => $property->floors,
                'noiseLevel' => $property->noiseLevel,
                'laundry' => $property->laundry,
                'internet' => $property->internet,
                'condition' => $property->condition,
                'video' => $property->video,
                'price' => (int) $property->price, // Ensure price is an integer
                'priceType' => $property->priceType,
                'likes' => $property->likes,
                'propertyStatus' => $property->propertyStatus,
                'adminId' => $property->adminId,
                'view' => $property->view, 
                'outdoor' => $property->outdoor,
                'propertyStyle' => $property->propertyStyle,
                'securityFeatures' => $property->securityFeatures,
                'amenities' => $property->amenities,
                'heating' => $property->heating,
                'cooling' => $property->cooling,
                'powerBackup' => $property->powerBackup,
                'nearbyInfrastructure' => $property->nearbyInfrastructure,
                'images' => $property->images->map(fn($image) => $image['image'])->toArray(),
                'location' => $property->location ? [
                    'latitude' => $property->location->latitude,
                    'longitude' => $property->location->longitude,
                    'region' => $property->location->region,
                ] : null,
            ];
        });

        $project->priceRange = $priceRange;

        return response()->json([
            'project' => $project,
            'properties' => $properties
        ], 200);
    }

    return response()->json($project, 200);
}

    
    


    public function update(Request $request, $id)
    {
        $owner = Project::findOrFail($id);
        $data = $request->all();
        $owner->update($data);
        return response()->json($owner);
    }

        public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();
        return response()->json(['message' => 'Project deleted successfully'], 200);
    }
    
    
    
        public function search(Request $request)
    {
        $query = Project::query();
    
        if ($request->filled('minPrice') && $request->filled('maxPrice')) {
            $query->whereBetween('price', [$request->minPrice, $request->maxPrice]);
        } elseif ($request->filled('minPrice')) {
            $query->where('price', '>=', $request->minPrice);
        } elseif ($request->filled('maxPrice')) {
            $query->where('price', '<=', $request->maxPrice);
        }
    
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $radiusInMiles = $request->query('radius', 10);
            $radiusInMeters = $radiusInMiles * 1609.34; 
        
            $query->whereRaw("
                ST_Distance_Sphere(
                    point(longitude, latitude), 
                    point(?, ?)
                ) <= ?", [$longitude, $latitude, $radiusInMeters]);  
        }
        


        if ($request->filled('region')) {
            $query->where('region', 'LIKE', "%{$request->region}%");
        }
        if ($request->has('delivery_time')) {
            $deliveryTime = $request->query('delivery_time');
        
            if (!empty($deliveryTime)) {
                $query->where('delivery_time', '=', $deliveryTime);
            }
        }
        if ($request->filled('reason')) {
            $reason = $request->query('reason');
            $query->whereRaw("JSON_CONTAINS(investmentReason, ?)", [json_encode($reason)]);
        }
        
    
        $projects = $query->get();
    
        return response()->json($projects, 200);
    }
}
