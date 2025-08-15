<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Project;
use App\Models\PropertyImage;
use App\Models\PropertyLocation;
use App\Models\HeroSectionFeaturedProperty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    function transformPropertyResponse($property)
    {
        return [
            "id" => $property['id'],
            "reference_no" => $property['reference_no'],
            "title" => $property['title'],
            "description" => $property['description'],
            "bedrooms" => $property['bedrooms'],
            "bathrooms" => $property['bathrooms'],
            "area" => $property['area'],
            "leaseTerm" => $property['leaseTerm'],
            'parkingSpace' => $property['parkingSpace'],
            "propertyType" => $property['propertyType'],
            "propertyStatus" => $property['propertyStatus'],
            "category" => $property['category'],
            "dealType" => $property['dealType'],
            "floors" => $property['floors'],
            "noiseLevel" => $property['noiseLevel'],
            "laundry" => $property['laundry'],
            "administrationFee" => $property['administrationFee'],
            "internet" => $property['internet'],
            "condition" => $property['condition'],
            "video" => $property['video'],
            'dateBuilt' => $property['dateBuilt'],
            'neighborhood' => $property->neighborhood,
            "price" => round((float) $property['price'], 2),
            "view" => is_array($property['view']) ? $property['view'] : [$property['view']],
            "outdoor" => is_array($property['outdoor']) ? $property['outdoor'] : [$property['outdoor']],
            "propertyStyle" => is_array($property['propertyStyle']) ? $property['propertyStyle'] : [$property['propertyStyle']],
            "securityFeatures" => is_array($property['securityFeatures']) ? $property['securityFeatures'] : [$property['securityFeatures']],
            "amenities" => is_array($property['amenities']) ? $property['amenities'] : [$property['amenities']],
            "heating" => is_array($property['heating']) ? $property['heating'] : [$property['heating']],
            "cooling" => is_array($property['cooling']) ? $property['cooling'] : [$property['cooling']],
            "powerBackup" => is_array($property['powerBackup']) ? $property['powerBackup'] : [$property['powerBackup']],
            "nearbyInfrastructure" => is_array($property['nearbyInfrastructure']) ? $property['nearbyInfrastructure'] : [$property['nearbyInfrastructure']],
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
            "admin" => $property->admin ? [
                "id" => $property->admin->id,
                "name" => $property->admin->username,
                "phone" => $property->admin->phone,
                "email" => $property->admin->email,
                "whatsapp" => $property->admin->whatsapp,
                "profile_image" => $property->admin->profile_image,
                "created_at" => $property->admin->created_at,
                "updated_at" => $property->admin->updated_at
            ] : null
        ];
    }
    private function transformProjectResponse($project)
    {
        return [
            'id' => $project->id,
            'title' => $project->title,
            'description' => $project->description,
            'projectType' => $project->projectType,
            'price' => round((float) $project->price, 2),
            'images' => $project->images,
            'videos' => $project->videos,
            'address' => $project->address,
            'housingType' => $project->housingType,
            'projectStatus' => $project->projectStatus,
            'longitude' => $project->longitude,
            'latitude' => $project->latitude,
            'region' => $project->region,
            'developerInformation' => $project->developerInformation,
            'neighborhood' => $project->neighborhood,
            'communityFeatures' => $project->communityFeatures,
            'sustainabilityFeatures' => $project->sustainabilityFeatures,
            'investmentReason' => $project->investmentReason,
            'waterHeater' => $project->waterHeater,
            'coolingSystem' => $project->coolingSystem,
            'internet' => $project->internet,
            'powerBackup' => $project->powerBackup,
            'nearbyInfrastructure' => $project->nearbyInfrastructure,
            'amenities' => $project->amenities,
            'progress' => $project->progress,
            'investmentPotential' => $project->investmentPotential,
            'FAQ' => $project->FAQ,
            'delivery_time' => $project->delivery_time,
        ];
    }

    public function index()
    {
        $properties = Property::with(['images', 'location', 'admin'])->get();

        $properties = $properties->sortBy(function ($property) {
            return strtolower($property->title);
        });

        $properties = $properties->map(function ($property) {
            return $this->transformPropertyResponse($property);
        })->values();

        return response()->json($properties);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'adminId' => 'required|integer|exists:admins,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'area' => 'required',
            'propertyType' => 'required|string|max:255',
            'dealType' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'location.longitude' => 'nullable|numeric',
            'location.latitude' => 'nullable|numeric',
            'location.region' => 'nullable|string|max:255',
            'propertyStatus' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:255',
            'video' => 'nullable|string',
            'parkingSpace' => 'nullable|integer|min:0',
            'administrationFee' => 'nullable|numeric|min:0',
            'dateBuilt' => 'nullable|string',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $reference = 'PROP-' . strtoupper(Str::random(8));
        while (Property::where('reference_no', $reference)->exists()) {
            $reference = 'PROP-' . strtoupper(Str::random(8));
        }
        $data = $request->except(['images', 'location']);
        $data['reference_no'] = $reference;

        $property = Property::create($data);


        if ($request->has('images')) {
            foreach ($request->images as $image) {
                PropertyImage::create(['property_id' => $property->id, 'image' => $image]);
            }
        }

        if ($request->has('location')) {
            PropertyLocation::create(array_merge($request->location, ['property_id' => $property->id]));
        }

        $property->load(['images', 'location']);
        $property = $this->transformPropertyResponse($property);

        return response()->json($property, 201);
    }

    public function show($id)
    {
        $property = Property::with(['images', 'location', 'admin'])->findOrFail($id);

        $property = $this->transformPropertyResponse($property);

        $dealType = $property['dealType'];
        $condition = $property['condition'];

        $similarProperties = Property::with(['images', 'location', 'admin'])
            ->where('dealType', $dealType)
            ->where('condition', $condition)
            ->where('id', '!=', $id)
            ->limit(9)
            ->get();

        $similarProperties = $similarProperties->map(function ($item) {
            return $this->transformPropertyResponse($item);
        });

        return response()->json([
            'property' => $property,
            'similarProperties' => $similarProperties
        ]);
    }



    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'area' => 'nullable',
            'propertyType' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'dealType' => 'nullable|string|max:255',
            'parkingSpace' => 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'neighborhood' => 'nullable|string',
            'location.longitude' => 'nullable|numeric',
            'location.latitude' => 'nullable|numeric',
            'location.region' => 'nullable|string|max:255',
            'administrationFee' => 'nullable|numeric|min:0',
            'dateBuilt' => 'nullable|string',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $property = Property::findOrFail($id);
        $property->update($request->except(['images', 'location']));

        if ($request->has('images')) {
            PropertyImage::where('property_id', $id)->delete();
            foreach ($request->images as $image) {
                PropertyImage::create(['property_id' => $id, 'image' => $image]);
            }
        }

        if ($request->has('location')) {
            PropertyLocation::updateOrCreate(
                ['property_id' => $id],
                $request->location
            );
        }

        $property->load(['images', 'location']);
        $property = $this->transformPropertyResponse($property);

        return response()->json($property);
    }

    public function destroy($id)
    {
        $property = Property::findOrFail($id);
        $property->delete();

        return response()->json(['message' => 'Property deleted successfully.']);
    }


    public function search(Request $request)
    {
        $query = Property::with(['images', 'location']);

        $latitude = $request->query('latitude');
        $longitude = $request->query('longitude');
        $radiusInMiles = $request->query('radius', 10);
        $radiusInMeters = $radiusInMiles * 1609.34;
        if ($request->has('locations')) {
            $locations = json_decode($request->query('locations'), true);
            $radiusInMiles = $request->query('radius', 10);
            $radiusInMeters = $radiusInMiles * 1609.34;

            if (is_array($locations) && count($locations) > 0) {
                $query->whereHas('location', function ($subQuery) use ($locations, $radiusInMeters) {
                    $subQuery->where(function ($locQuery) use ($locations, $radiusInMeters) {
                        foreach ($locations as $loc) {
                            if (isset($loc['latitude'], $loc['longitude'])) {
                                $latitude = $loc['latitude'];
                                $longitude = $loc['longitude'];
                                $locQuery->orWhereRaw("
                            ST_Distance_Sphere(
                                point(longitude, latitude),
                                point(?, ?)
                            ) <= ?
                        ", [$longitude, $latitude, $radiusInMeters]);
                            }
                        }
                    });
                });
            }
        }



        $filters = [
            'views' => 'view',
            'dealType' => 'dealType',
            'outdoor' => 'outdoor',
            'propertyStyle' => 'propertyStyle',
            //'leaseTerm' => 'leaseTerm',
            //'floors' => 'floors',
            // 'noiseLevel' => 'noiseLevel',
            //'laundry' => 'laundry',
            'amenities' => 'amenities',
            'internet' => 'internet',
            'heating' => 'heating',
            'cooling' => 'cooling',
            'securityFeatures' => 'securityFeatures',
            'powerBackup' => 'powerBackup',
            'nearbyInfrastructure' => 'nearbyInfrastructure',
        ];

        //propertyType filter
        /*if ($request->has('propertyType')) {
            $propertyTypes = json_decode($request->query('propertyType'), true);

            if (is_array($propertyTypes) && count($propertyTypes) > 0) {
                $query->where(function ($subQuery) use ($propertyTypes) {
                    foreach ($propertyTypes as $type) {
                        $subQuery->orWhere('propertyType', 'LIKE', '%' . $type . '%');
                    }
                });
            }
        }*/

        //propertyType filter
        if ($request->has('propertyType')) {

            $propertyTypes = json_decode($request->query('propertyType'), true);

            if (!is_array($propertyTypes)) {
                $propertyTypes = [$request->query('propertyType')];
            }

            if (count($propertyTypes) > 0) {
                $query->where(function ($subQuery) use ($propertyTypes) {
                    foreach ($propertyTypes as $type) {
                        $subQuery->orWhere('propertyType', '=', $type);
                    }
                });
            }
        }

        //dealType filter
if ($request->has('dealType')) {
    // Decode URL-encoded strings and normalize
    $dealType = strtolower(trim(urldecode($request->query('dealType'))));

    if (!empty($dealType)) {
        if ($dealType === 'new') {
            // All properties inside projects
            $projectPropertyIds = Project::pluck('properties')
                ->flatten()
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            if (!empty($projectPropertyIds)) {
                $query->whereIn('id', $projectPropertyIds);
            } else {
                $query->whereRaw('1 = 0');
            }

        } elseif ($dealType === 'rental') {
            $query->whereRaw('LOWER(dealType) = ?', ['rental']);

        } elseif ($dealType === 'residential rental') {
            $query->whereRaw('LOWER(dealType) = ?', ['residential rental']);

        } elseif ($dealType === 'tourist rental') {
            $query->whereRaw('LOWER(dealType) = ?', ['tourist rental']);

        } elseif (in_array($dealType, ['sale', 'sales'])) {
            $query->whereIn('dealType', ['sale', 'sales']);

        } else {
            $query->whereRaw('LOWER(dealType) = ?', [$dealType]);
        }
    }
}



        //propertyStatus filter
        if ($request->has('propertyStatus')) {
            $dealType = $request->query('propertyStatus');

            if (!empty($dealType)) {
                $query->where('propertyStatus', '=', $dealType);
            }
        }
        //condition filter 
        if ($request->has('condition')) {
            $condition = $request->query('condition');

            if (!empty($condition)) {
                $query->where('condition', '=', $condition);
            }
        }
        //protpe
        //beds filter

        if ($request->has('beds')) {
            $bedsFilter = json_decode($request->query('beds'), true);

            if (is_array($bedsFilter) && count($bedsFilter) > 0) {
                $query->where(function ($subQuery) use ($bedsFilter) {
                    foreach ($bedsFilter as $bed) {
                        if (str_ends_with($bed, '+')) {
                            $bedCount = (int) rtrim($bed, '+');
                            $subQuery->orWhere('bedrooms', '>=', $bedCount);
                        } elseif (is_numeric($bed)) {
                            $subQuery->orWhere('bedrooms', '=', (int) $bed);
                        }
                    }
                });
            }
        }

        //project properties 
        if ($request->query('project') === 'true') {
            $propertyIds = Project::pluck('properties')
                ->flatten()
                ->unique()
                ->filter()
                ->values();

            $properties = Property::with(['images', 'location', 'admin'])
                ->whereIn('id', $propertyIds)
                ->get();

            return response()->json([
                'data' => $properties->map(function ($property) {
                    return $this->transformPropertyResponse($property);
                }),
            ]);
        }


        //bathrooms filter


        if ($request->has('baths')) {
            $bathsFilter = json_decode($request->query('baths'), true);

            if (is_array($bathsFilter) && count($bathsFilter) > 0) {
                $query->where(function ($subQuery) use ($bathsFilter) {
                    foreach ($bathsFilter as $bath) {
                        if (str_ends_with($bath, '+')) {
                            $bathCount = (int) rtrim($bath, '+');
                            $subQuery->orWhere('bathrooms', '>=', $bathCount);
                        } elseif (is_numeric($bath)) {
                            $subQuery->orWhere('bathrooms', '=', (int) $bath);
                        }
                    }
                });
            }
        }

        //parking filter
        if ($request->has('parking')) {
            $parkingFilter = json_decode($request->query('parking'), true);

            if (is_array($parkingFilter) && count($parkingFilter) > 0) {
                $query->where(function ($subQuery) use ($parkingFilter) {
                    foreach ($parkingFilter as $parking) {
                        if (str_ends_with($parking, '+')) {
                            $parkingCount = (int) rtrim($parking, '+');
                            $subQuery->orWhere('parkingSpace', '>=', $parkingCount); // fixed column name
                        } elseif (is_numeric($parking)) {
                            $subQuery->orWhere('parkingSpace', '=', (int) $parking); // fixed column name
                        }
                    }
                });
            }
        }


        //bathrooms filter
        if ($request->has('baths')) {
            $bathsFilter = json_decode($request->query('baths'), true);

            if (is_array($bathsFilter) && count($bathsFilter) > 0) {
                $query->where(function ($subQuery) use ($bathsFilter) {
                    foreach ($bathsFilter as $bath) {
                        if (str_ends_with($bath, '+')) {
                            $bathCount = (int) rtrim($bath, '+');
                            $subQuery->orWhere('bathrooms', '>=', $bathCount);
                        } elseif (is_numeric($bath)) {
                            $subQuery->orWhere('bathrooms', '=', (int) $bath);
                        }
                    }
                });
            }
        }
        //leaseTerm filter

        if ($request->has('leaseTerm')) {
            $leaseTerm = json_decode($request->query('leaseTerm'), true);

            if (is_array($leaseTerm) && count($leaseTerm) > 0) {
                $query->where(function ($subQuery) use ($leaseTerm) {
                    foreach ($leaseTerm as $lease) {
                        $subQuery->orWhere('leaseTerm', 'LIKE', '%' . $lease . '%');
                    }
                });
            }
        }

        //floors filter

        if ($request->has('floors')) {
            $floorsFilter = json_decode($request->query('floors'), true);

            if (is_array($floorsFilter) && count($floorsFilter) > 0) {
                $query->where(function ($subQuery) use ($floorsFilter) {
                    foreach ($floorsFilter as $floor) {
                        if (str_ends_with($floor, '+')) {
                            $floorCount = (int) rtrim($floor, '+');
                            $subQuery->orWhere('floors', '>=', $floorCount);
                        } elseif (is_numeric($floor)) {
                            $subQuery->orWhere('floors', '=', (int) $floor);
                        }
                    }
                });
            }
        }
        //areSize filter 
        if ($request->filled('areaSize')) {
            $areaSize = $request->query('areaSize');

            if (is_numeric($areaSize)) {
                $areaValue = (int) $areaSize;
                $minNearbyArea = max(0, $areaValue - 10);  // prevent negative lower bound
                $maxNearbyArea = $areaValue + 10;

                $query->whereBetween('area', [$minNearbyArea, $maxNearbyArea]);
            }
        }

        //noise level filter

        if ($request->has('noiseLevel')) {
            $noiseLevel = json_decode($request->query('noiseLevel'), true);

            if (is_array($noiseLevel) && count($noiseLevel) > 0) {
                $query->where(function ($subQuery) use ($noiseLevel) {
                    foreach ($noiseLevel as $type) {
                        $subQuery->orWhere('noiseLevel', 'LIKE', '%' . $type . '%');
                    }
                });
            }
        }


        //laundry filter

        if ($request->has('laundry')) {
            $laundry = json_decode($request->query('laundry'), true);

            if (is_array($laundry) && count($laundry) > 0) {
                $query->where(function ($subQuery) use ($laundry) {
                    foreach ($laundry as $type) {
                        $subQuery->orWhere('laundry', 'LIKE', '%' . $type . '%');
                    }
                });
            }
        }

        //internet filter

        if ($request->has('internet')) {
            $internet = json_decode($request->query('internet'), true);

            if (is_array($internet) && count($internet) > 0) {
                $query->where(function ($subQuery) use ($internet) {
                    foreach ($internet as $type) {
                        $subQuery->orWhere('internet', 'LIKE', '%' . $type . '%');
                    }
                });
            }
        }

        //General filters

        foreach ($filters as $param => $column) {
            if ($request->has($param)) {
                $values = json_decode($request->query($param), true);

                if (is_array($values) && count($values) > 0) {
                    $query->where(function ($subQuery) use ($column, $values) {
                        foreach ($values as $value) {
                            $subQuery->orWhere($column, 'LIKE', '%' . $value . '%');
                        }
                    });
                }
            }
        }



        $minPrice = $request->query('minPrice', 0);
        $maxPrice = $request->query('maxPrice', PHP_INT_MAX);
        $minAdminPrice = $request->query('aminprice', 0);
        $maxAdminPrice = $request->query('amaxprice', PHP_INT_MAX);
        $query->whereBetween('administrationFee', [(float) $minAdminPrice, (float) $maxAdminPrice]);

        $query->whereBetween('price', [(float) $minPrice, (float) $maxPrice]);

        $fields = $request->query('fields', '*');
        $fieldsArray = $fields === '*' ? ['*'] : explode(',', $fields);
        $query->select($fieldsArray);

        $sortBy = $request->query('sortBy');
        if ($sortBy === 'lowestprice') {
            $query->orderBy('price', 'asc'); // Sort by lowest price
        } elseif ($sortBy === 'highestprice') {
            $query->orderBy('price', 'desc'); // Sort by highest price
        } elseif ($sortBy === 'recent') {
            $query->orderBy('created_at', 'desc'); // Sort by most recent
        }

        $properties = $query->get();

        return response()->json([
            'data' => $properties->map(function ($property) {
                return $this->transformPropertyResponse($property);
            }),
        ]);
    }

    public function incrementViews($id)
    {
        $property = Property::findOrFail($id);
        $property->increment('views');

        return response()->json(['message' => 'Views incremented successfully.', 'views' => $property->views]);
    }

    public function incrementLikes($id)
    {
        $property = Property::findOrFail($id);
        $property->increment('likes');

        return response()->json(['message' => 'Likes incremented successfully.', 'likes' => $property->likes]);
    }


    public function getFilteredProperties()
    {
        $mostViewedProperties = Property::with(['images', 'location'])
            ->orderByDesc('views')
            ->take(12)
            ->get();

        $rentProperties = Property::with(['images', 'location'])
            ->whereIn('dealType', ['Residential rental', 'rental', 'tourist rental'])
            ->orderByDesc('views')
            ->take(12)
            ->get();


        $saleProperties = Property::with(['images', 'location'])
            ->where('dealType', 'sale')
            ->orderByDesc('views')
            ->take(12)
            ->get();
        $newDevelopments = Project::orderByDesc('created_at')
            ->take(12)
            ->get()
            ->map(fn($project) => $this->transformProjectResponse($project));

        $usedSaleProperties = Property::with(['images', 'location'])
            ->where('dealType', 'sale')
            ->where('condition', 'used')
            ->orderByDesc('views')
            ->take(12)
            ->get();
        $mostViewedProperties = $mostViewedProperties->map(fn($property) => $this->transformPropertyResponse($property));
        $rentProperties = $rentProperties->map(fn($property) => $this->transformPropertyResponse($property));
        $saleProperties = $saleProperties->map(fn($property) => $this->transformPropertyResponse($property));
        $usedSaleProperties = $usedSaleProperties->map(fn($property) => $this->transformPropertyResponse($property));

        return response()->json([
            'mostViewed' => $mostViewedProperties, // most viewed properties
            'rent' => $rentProperties, // rent properties
            'saleProperties' => $saleProperties, // new for sale properties
            //     'usedSale' => $usedSaleProperties, // used for sale properties
            'newDevelopments' => $newDevelopments,

        ]);
    }


    public function heroSection()
    {
        $MAX_HERO_PROPERTIES = 12;

        $featuredPropertyIds = HeroSectionFeaturedProperty::pluck('property_id')->toArray();

        $heroProperties = Property::with(['images', 'location', 'admin'])
            ->whereIn('id', $featuredPropertyIds)
            ->get();

        $heroCount = $heroProperties->count();

        if ($heroCount < $MAX_HERO_PROPERTIES) {
            $remainingCount = $MAX_HERO_PROPERTIES - $heroCount;

            $nonHeroProperties = Property::with(['images', 'location', 'admin'])
                ->whereNotIn('id', $featuredPropertyIds)
                ->orderByDesc('views')
                ->orderByDesc('price')
                ->orderByDesc('created_at')
                ->take($remainingCount)
                ->get();

            $finalProperties = $heroProperties->merge($nonHeroProperties);
        } else {
            $finalProperties = $heroProperties->take($MAX_HERO_PROPERTIES);
        }

        $finalProperties = $finalProperties->map(fn($property) => $this->transformPropertyResponse($property));

        return response()->json($finalProperties);
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
        $heroProperties = $heroProperties->map(fn($property) => $this->transformPropertyResponse($property));
        $nonHeroProperties = $nonHeroProperties->map(fn($property) => $this->transformPropertyResponse($property));

        return response()->json([
            'heroProperties' => $heroProperties,
            'nonHeroProperties' => $nonHeroProperties,
        ]);
    }
}
