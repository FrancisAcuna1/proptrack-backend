<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipments extends Model
{
    use HasFactory;
    protected $table = 'equipments';

    const CREATED_AT = null;
    const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    // public function apartments()
    // {
    //     return $this->hasMany(ApartmentInclusion::class);
    // }// old relation

    // public function boardingHouses()
    // {
    //     return $this->hasMany(BoardingHouseInclusion::class);
    // } // old relation

    public function inclusions()
    {
        return $this->hasMany(Inclusion::class, 'equipment_id');
    }

}
