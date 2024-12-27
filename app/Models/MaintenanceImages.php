<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceImages extends Model
{
    use HasFactory;
    protected $table = 'maintenance_images';

    const CREATED_AT = true;
    const UPDATED_AT = true;
    public $timestamps = false;

    protected $fillable = [
        'image_path',
        'maintenance_id',
        'expenses_id',
    ];

    public function maintenanceRequest(){
        return $this->belongsTo(MaintenanceRequest::class, 'maintenance_id');
    }

    public function expenses(){
        return $this->belongsTo(Expenses::class, 'expenses_id');
    }

}
