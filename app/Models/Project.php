<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'images', 'projectType', 'price', 'description', 'videos',
        'address', 'longitude', 'latitude', 'region', 'developerInformation',
        'neighborhood', 'communityFeatures', 'sustainabilityFeatures',
        'investmentReason', 'amenities', 'progress', 'investmentPotential', 'FAQ','properties',
        'delivery_time'
    ];

    protected $casts = [
        'images' => 'array',
        'videos' => 'array',
        'neighborhood' => 'array',
        'communityFeatures' => 'array',
        'sustainabilityFeatures' => 'array',
        'investmentReason' => 'array',
        'amenities' => 'array',
        'FAQ' => 'array',
        'properties' => 'array'
    ];
}
