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
        'status',
        'property_type',
        'building_no',
        'street',
        'barangay',
        'municipality',
        // 'image'
    ];

    protected $casts = [
        'images' => 'array'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class, 'boardinghouse_id');
    }

    // public function inclusions()
    // {
    //     return $this->hasMany(BoardingHouseInclusion::class, 'boardinghouse_id');
    // }

    public function rentalAgreements()
    {
        return $this->morphMany(RentalAgreement::class, 'rented_unit');
    }

    public function inclusions()
    {
        return $this->morphMany(Inclusion::class, 'unit');
    }

    public function images()
    {
        return $this->morphMany(PropertiesImage::class, 'unit');
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransactions::class, 'rented_unit_id');
    }

    public function expenses()
    {
        return $this->morphMany(Expenses::class, 'unit');
    }
    
    public function maintenance_schedule()
    {
        return $this->morphMany(ScheduleMaintenance::class, 'unit');
    }




}
