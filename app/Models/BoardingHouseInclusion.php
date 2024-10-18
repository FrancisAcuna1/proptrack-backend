<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardingHouseInclusion extends Model
{
    use HasFactory;
    protected $table = 'boardinghouse_inclusions';

    const CREATED_AT = null;
    const UPDATED_AT = null;
    public $timestamps = false;
    protected $fillable = [
        'boardinghouse_id', 
        'inclusion_id',
        'quantity'
    ];

    public function boardinghouse()
    {
        return $this->belongsTo(BoardingHouse::class);
    }

    public function inclusion()
    {
        return $this->belongsTo(Equipments::class);
    }
}

