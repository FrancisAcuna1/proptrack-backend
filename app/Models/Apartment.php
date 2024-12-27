<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    use HasFactory;
    protected $table = 'apartment';

    const CREATED_AT = null;
    const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'property_id',
        'apartment_name',
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
        // 'image'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class); // Belongs to property
    }

    // public function inclusions()
    // {
    //     return $this->hasMany(ApartmentInclusion::class, 'apartment_id'); // have a relation to ApartmentInclusion Model
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
