<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApartmentInclusion extends Model
{
    use HasFactory;
    protected $table = 'apartment_inclusions';

    const CREATED_AT = null;
    const UPDATED_AT = null;
    public $timestamps = false;
    protected $fillable = [
        'apartment_id', 
        'inclusion_id',
        'quantity'
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class); // Belongs or have a relation to Apartment Model
    }

    public function inclusion()
    {
        return $this->belongsTo(Equipments::class); // connected or have a relation to Equipments Model
    }
}
