<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardingHouse extends Model
{
    use HasFactory;
    protected $table = 'boarding_houses';

    const CREATED_AT = null;
    const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'property_id',
        'boarding_house_name',
        'number_of_rooms',
        'capacity',
        'rental_fee',
        'payor_name',
        'status',
        'property_type',
        'building_no',
        'street',
        'barangay',
        'municipality',
        'image'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class, 'boardinghouse_id');
    }

    public function inclusions()
    {
        return $this->hasMany(BoardingHouseInclusion::class, 'boardinghouse_id');
    }

    public function rentalAgreements()
    {
        return $this->morphMany(RentalAgreement::class, 'rented_unit');
    }

}
