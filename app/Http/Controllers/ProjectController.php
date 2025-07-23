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
                'housingType' => $project->housingType,
                'projectStatus' => $project->projectStatus,
                'waterHeater' => $project->waterHeater,
                'coolingSystem' => $project->coolingSystem,
                'internet' => $project->internet,
                'powerBackup' => $project->powerBackup,
                'nearbyInfrastructure' => $project->nearbyInfrastructure,
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
            'properties' => 'nullable|array',
            'housingType' => 'nullable|string',
            'projectStatus' => 'nullable|string',
            'waterHeater' => 'nullable|string',
            'coolingSystem' => 'nullable|string',
            'internet' => 'nullable|string',
            'powerBackup' => 'nullable|string',
            'nearbyInfrastructure' => 'nullable|array',
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
            $minArea = $properties->min('area');
            $maxArea = $properties->max('area');
            $areaRange = "$minArea - $maxArea";

            $minBedrooms = $properties->min('bedrooms');
            $maxBedrooms = $properties->max('bedrooms');
            $bedroomRange = "$minBedrooms - $maxBedrooms";

            $minBathrooms = $properties->min('bathrooms');
            $maxBathrooms = $properties->max('bathrooms');
            $bathroomRange = "$minBathrooms - $maxBathrooms";

            $minFloors = $properties->min('floors');
            $maxFloors = $properties->max('floors');
            $floorRange = "$minFloors - $maxFloors";

            $minParking = $properties->min('parkingSpace');
            $maxParking = $properties->max('parkingSpace');
            $parkingRange = "$minParking - $maxParking";
            $minAdminFee = $properties->min('administrationFee');
            $maxAdminFee = $properties->max('administrationFee');
            $adminFeeRange = "$minAdminFee - $maxAdminFee";

            $properties = $properties->map(function ($property) {
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'description' => $property->description,
                    'bedrooms' => $property->bedrooms,
                    'bathrooms' => $property->bathrooms,
                    "reference_no" => $property->reference_no,
                    'area' => $property->area,
                    'administrationFee' => $property->administrationFee,
                    'propertyType' => $property->propertyType,
                    'category' => $property->category,
                    'dealType' => $property->dealType,
                    'leaseTerm' => $property->leaseTerm,
                    'parkingSpace' => $property->parkingSpace,
                    'dateBuilt' => $property->dateBuilt,
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
            $project->areaRange = $areaRange;
            $project->bedroomRange = $bedroomRange;
            $project->bathroomRange = $bathroomRange;
            $project->floorRange = $floorRange;
            $project->parkingRange = $parkingRange;
            $project->adminFeeRange = $adminFeeRange;

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

    public function projectProperties()
    {
        $projects = Project::all();
        $allProperties = collect();

        foreach ($projects as $project) {
            if (!empty($project->properties)) {
                $propertyIds = $project->properties;

                $properties = Property::with(['images', 'location'])
                    ->whereIn('id', $propertyIds)
                    ->get()
                    ->map(fn($property) => $this->transformProperty($property));

                $allProperties = $allProperties->merge($properties);
            }
        }

        return response()->json($allProperties->values(), 200);
    }
    private function transformProperty($property)
    {
        return [
            'id' => $property->id,
            'reference_no' => $property->reference_no,
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
            'parkingSpace' => $property->parkingSpace,
            'dateBuilt' => $property->dateBuilt,
            'internet' => $property->internet,
            'condition' => $property->condition,
            'video' => $property->video,
            'price' => (int) $property->price,
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
            'administrationFee' => $property->administrationFee,
            'powerBackup' => $property->powerBackup,
            'nearbyInfrastructure' => $property->nearbyInfrastructure,
            'images' => $property->images->map(fn($image) => $image['image'])->toArray(),
            'location' => $property->location ? [
                'latitude' => $property->location->latitude,
                'longitude' => $property->location->longitude,
                'region' => $property->location->region,
            ] : null,
        ];
    }
    public function searchProjectProperties(Request $request)
    {
        $query = Property::with(['images', 'location']);

        // Get all property IDs from all projects
        $projectPropertyIds = Project::pluck('properties')->flatten()->unique()->filter()->values();

        // Restrict query to only those properties
        $query->whereIn('id', $projectPropertyIds);

        // Common filters (copy-paste from your `search()` method)
        $latitude = $request->query('latitude');
        $longitude = $request->query('longitude');
        $radiusInMiles = $request->query('radius', 10);
        $radiusInMeters = $radiusInMiles * 1609.34;

        if (!empty($latitude) && !empty($longitude)) {
            $query->whereHas('location', function ($subQuery) use ($latitude, $longitude, $radiusInMeters) {
                $subQuery->whereRaw("
                ST_Distance_Sphere(
                    point(longitude, latitude),
                    point(?, ?)
                ) <= ?", [$longitude, $latitude, $radiusInMeters]);
            });
        }

        $filters = [
            'views' => 'view',
            'dealType' => 'dealType',
            'outdoor' => 'outdoor',
            'propertyStyle' => 'propertyStyle',
            'amenities' => 'amenities',
            'internet' => 'internet',
            'heating' => 'heating',
            'cooling' => 'cooling',
            'securityFeatures' => 'securityFeatures',
            'powerBackup' => 'powerBackup',
            'nearbyInfrastructure' => 'nearbyInfrastructure',
        ];

        // Apply filters like propertyType, dealType, etc.
        if ($request->has('propertyType')) {
            $query->where('propertyType', $request->query('propertyType'));
        }

        if ($request->has('dealType')) {
            $dealType = $request->query('dealType');
            if ($dealType === 'Rental') {
                $query->where(function ($subQuery) {
                    $subQuery->where('dealType', 'Rental')
                        ->orWhere('dealType', 'Residential Rental')
                        ->orWhere('dealType', 'Tourist Rental');
                });
            } else {
                $query->where('dealType', '=', $dealType);
            }
        }

        if ($request->has('propertyStatus')) {
            $query->where('propertyStatus', $request->query('propertyStatus'));
        }

        if ($request->has('condition')) {
            $query->where('condition', $request->query('condition'));
        }

        // Bedrooms Filter
        if ($request->has('beds')) {
            $beds = json_decode($request->query('beds'), true);
            if (is_array($beds)) {
                $query->where(function ($subQuery) use ($beds) {
                    foreach ($beds as $bed) {
                        if (str_ends_with($bed, '+')) {
                            $subQuery->orWhere('bedrooms', '>=', (int) rtrim($bed, '+'));
                        } else {
                            $subQuery->orWhere('bedrooms', '=', (int) $bed);
                        }
                    }
                });
            }
        }

        // Bathrooms Filter
        if ($request->has('baths')) {
            $baths = json_decode($request->query('baths'), true);
            if (is_array($baths)) {
                $query->where(function ($subQuery) use ($baths) {
                    foreach ($baths as $bath) {
                        if (str_ends_with($bath, '+')) {
                            $subQuery->orWhere('bathrooms', '>=', (int) rtrim($bath, '+'));
                        } else {
                            $subQuery->orWhere('bathrooms', '=', (int) $bath);
                        }
                    }
                });
            }
        }

        // General filters from reusable array
        foreach ($filters as $param => $column) {
            if ($request->has($param)) {
                $values = json_decode($request->query($param), true);
                if (is_array($values)) {
                    $query->where(function ($subQuery) use ($column, $values) {
                        foreach ($values as $value) {
                            $subQuery->orWhere($column, 'LIKE', '%' . $value . '%');
                        }
                    });
                }
            }
        }

        // Price Range
        $minPrice = $request->query('minPrice', 0);
        $maxPrice = $request->query('maxPrice', PHP_INT_MAX);
        $query->whereBetween('price', [(float) $minPrice, (float) $maxPrice]);

        // Optional field selection
        $fields = $request->query('fields', '*');
        $fieldsArray = $fields === '*' ? ['*'] : explode(',', $fields);
        $query->select($fieldsArray);

        // Final fetch
        $properties = $query->get();

        return response()->json([
            'data' => $properties->map(fn($property) => $this->transformProperty($property)),
        ]);
    }

}
