<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentedUnitDetails extends Model
{
    use HasFactory;
    protected $table = 'rented_unit_details';

    const CREATED_AT = null;
    const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'rental_agreement_id',
        'room_id',
        'rented_bed_number',
    ];

    public function rentalagreement(){
        return $this->belongsTo(RentalAgreement::class, 'rental_agreement_id');
    }

    public function rentedroom()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
}
