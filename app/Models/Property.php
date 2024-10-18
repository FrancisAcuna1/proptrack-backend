<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Property extends Model
{
    use HasFactory;
    protected $table = 'property';

    const CREATED_AT = null;
    const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'propertyname',
        'barangay',
        'municipality',
        'image'
    ];

    public function boardingHouses()
    {
        return $this->hasMany(BoardingHouse::class);
    }

    // A Property can have many Apartments
    public function apartments()
    {
        return $this->hasMany(Apartment::class);
    }

        
}
