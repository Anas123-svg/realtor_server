<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'username',
        'email',
        'password',
        'phone',
        'whatsapp',
        'profile_image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function properties()
{
    return $this->hasMany(Property::class, 'adminId');
}

}
