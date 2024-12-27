<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    use HasFactory;
    protected $table = 'beds';

    const CREATED_AT = null;
    const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'room_id',
        'bed_number',
        'price',
        'status',
    ];

    public function rooms()
    {
        return $this->belongsTo(room::class, 'room_id'); // Belongs or have a relation to Apartment Model
    }


}
