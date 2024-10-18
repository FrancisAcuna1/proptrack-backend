<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    protected $table = 'rooms';

    const CREATED_AT = null;
    const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'boardinghouse_id',
        'room_number',
        'number_of_beds',
    ];

    public function boardinghouse()
    {
        return $this->belongsTo(BoardingHouse::class, 'boardinghouse_id');
    }

    public function beds()
    {
        return $this->hasMany(Bed::class, 'room_id'); // have a relation to ApartmentInclusion Model
    }
}
