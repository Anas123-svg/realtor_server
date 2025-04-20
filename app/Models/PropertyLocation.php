<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PropertyLocation extends Model
{
    use HasFactory;

    protected $fillable = ['property_id', 'longitude', 'latitude', 'region'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

}
