<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;


class Property extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'title',
        'description',
        'bedrooms',
        'bathrooms',
        'area',
        'propertyType',
        'category',
        'dealType',
        'leaseTerm',
        'floors',
        'noiseLevel',
        'laundry',
        'internet',
        'condition',
        'video',
        'price',
        'view',
        'outdoor',
        'propertyStyle',
        'securityFeatures',
        'amenities',
        'heating',
        'cooling',
        'priceType',
        'views',
        'likes',
        'powerBackup',
        'nearbyInfrastructure',
        'propertyStatus',
        'adminId',
        'neighborhood'
    ];

    protected $casts = [
        'view' => 'array',
        'outdoor' => 'array',
        'propertyStyle' => 'array',
        'securityFeatures' => 'array',
        'amenities' => 'array',
        'heating' => 'array',
        'cooling' => 'array',
        'powerBackup' => 'array',
        'nearbyInfrastructure'=> 'array',
    ];
    public function images()
    {
        return $this->hasMany(PropertyImage::class, 'property_id');
    }

    public function location()
    {
        return $this->hasOne(PropertyLocation::class, 'property_id');
    }
    public function admin()
{
    return $this->belongsTo(Admin::class, 'adminId');
}

}
