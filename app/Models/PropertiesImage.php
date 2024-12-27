<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertiesImage extends Model
{
    use HasFactory;
    protected $table = 'property_images';

    const CREATED_AT = null;
    const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'image_path',
        'unit_id',
        'unit_type',
    ];

    public function unit()
    {
        return $this->morphTo();
    }
}
