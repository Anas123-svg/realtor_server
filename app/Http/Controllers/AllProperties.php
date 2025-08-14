<?php

namespace App\Http\Controllers;
use App\Models\Property;
use App\Models\Project;
use App\Models\PropertyImage;
use App\Models\PropertyLocation;
use App\Models\HeroSectionFeaturedProperty;

use Illuminate\Http\Request;

class AllProperties extends Controller
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

    public function search(Request $request)
    {
        $dealType = $request->query('dealType');
        $minPrice = $request->query('minPrice');
        $maxPrice = $request->query('maxPrice');
        $areaSize = $request->query('areaSize');
        $latitude = $request->query('latitude');
        $longitude = $request->query('longitude');
        $radiusInMiles = $request->query('radius', 10);
        $radiusInMeters = $radiusInMiles * 1609.34;
        $region = $request->query('region');

        $propertiesQuery = Property::with(['images', 'location', 'admin']);
        //location
        // Add locations radius filter if provided
        if ($request->has('locations')) {
            $locations = json_decode($request->query('locations'), true);
            if (is_array($locations) && count($locations) > 0) {
                $propertiesQuery->whereHas('location', function ($subQuery) use ($locations, $radiusInMeters) {
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
        // Deal type handling
        if (strtolower($dealType) === 'new') {
            $projectPropertyIds = Project::pluck('properties')->flatten()->filter()->toArray();
            $propertiesQuery->whereIn('id', $projectPropertyIds);
        } elseif (strtolower($dealType) === 'rental') {
            $propertiesQuery->whereIn('dealType', ['rental', 'residential rental', 'tourist rental']);
        } elseif (strtolower($dealType) === 'sales') {
            $propertiesQuery->where('dealType', 'sales');
        }

        // Price filters
        if ($minPrice) {
            $propertiesQuery->where('price', '>=', $minPrice);
        }
        if ($maxPrice) {
            $propertiesQuery->where('price', '<=', $maxPrice);
        }

        // Baths filter
        if ($request->filled('baths')) {
            $bathValue = $request->query('baths');

            $propertiesQuery->where(function ($subQuery) use ($bathValue) {
                if (str_ends_with($bathValue, '+')) {
                    $bathCount = (int) rtrim($bathValue, '+');
                    $subQuery->where('bathrooms', '>=', $bathCount);
                } elseif (is_numeric($bathValue)) {
                    $subQuery->where('bathrooms', '=', (int) $bathValue);
                }
            });
        }

        // Beds filter
        if ($request->filled('beds')) {
            $bedValue = $request->query('beds');

            $propertiesQuery->where(function ($subQuery) use ($bedValue) {
                if (str_ends_with($bedValue, '+')) {
                    $bedCount = (int) rtrim($bedValue, '+');
                    $subQuery->where('bedrooms', '>=', $bedCount);
                } elseif (is_numeric($bedValue)) {
                    $subQuery->where('bedrooms', '=', (int) $bedValue);
                }
            });
        }



        // Area size filter
        if ($request->filled('areaSize') && is_numeric($areaSize)) {
            $areaValue = (int) $areaSize;
            $minNearbyArea = max(0, $areaValue - 10);  // avoid negative area
            $maxNearbyArea = $areaValue + 10;

            // Filter properties where area is between [areaValue-10, areaValue+10]
            $propertiesQuery->whereBetween('area', [$minNearbyArea, $maxNearbyArea]);
        }

        //property type
        if ($request->has('propertyType')) {
            $propertyTypes = json_decode($request->query('propertyType'), true);
            if (!is_array($propertyTypes)) {
                $propertyTypes = [$request->query('propertyType')];
            }
            if (count($propertyTypes) > 0) {
                $propertiesQuery->where(function ($subQuery) use ($propertyTypes) {
                    foreach ($propertyTypes as $type) {
                        $subQuery->orWhere('propertyType', '=', $type);
                    }
                });
            }
        }

        // Get results
        $properties = $propertiesQuery->get()
            ->sortBy(fn($property) => strtolower($property->title))
            ->map(fn($property) => $this->transformPropertyResponse($property))
            ->values();

        return response()->json($properties);
    }

public function search2(Request $request) 
{
    $dealType = $request->input('dealType');

    $frontendBaseUrl = 'http://localhost:3000/properties';

    $propertyType = $request->input('propertyType');
    if ($propertyType) {
        $propertyType = json_decode($propertyType, true); // decode string to array
    }

    $locations = $request->input('locations');
    if ($locations) {
        $locations = json_decode($locations, true); // decode string to array
    }

    $minPrice = $request->input('minPrice');
    $maxPrice = $request->input('maxPrice');
    $beds = $request->input('beds');
    $baths = $request->input('baths');
    $areaSize = $request->input('areaSize');

    // Base URLs depending on dealType
    $url = $frontendBaseUrl;
    switch ($dealType) {
        case 'Sale':
            $url .= '?condition=Used&dealType=Sale';
            break;
        case 'TouristRental':
            $url .= '?dealType=Tourist%20Rental';
            break;
        case 'ResidentialRental':
            $url .= '?dealType=Residential%20Rental';
            break;
        case 'New':
            $url .= '?dealType=New';
            break;
        default:
            $url .= '?condition=Used&dealType=Sale';
            break;
    }

    $queryParams = [];

    // Encode propertyType array
    if (!empty($propertyType)) {
        $encodedTypes = array_map(fn($type) => str_replace(' ', '+', $type), $propertyType);
        $queryParams['propertyType'] = json_encode($encodedTypes);
    }

    // Encode locations array
    if (!empty($locations)) {
        $encodedLocations = array_map(function($loc) {
            if (isset($loc['label'])) {
                $loc['label'] = str_replace(' ', '+', $loc['label']);
            }
            if (isset($loc['region'])) {
                $loc['region'] = str_replace(' ', '+', $loc['region']);
            }
            return $loc;
        }, $locations);

        $queryParams['locations'] = json_encode($encodedLocations);
    }

    // Plain minPrice and maxPrice
    if ($minPrice) $queryParams['minPrice'] = $minPrice;
    if ($maxPrice) $queryParams['maxPrice'] = $maxPrice;

    // Encode beds and baths as JSON arrays
    if ($beds) $queryParams['beds'] = json_encode([$beds]);
    if ($baths) $queryParams['baths'] = json_encode([$baths]);

    if ($areaSize) $queryParams['areaSize'] = $areaSize;

    if (!empty($queryParams)) {
        $url .= '&' . http_build_query($queryParams);
    }

    return $url;
}




}
