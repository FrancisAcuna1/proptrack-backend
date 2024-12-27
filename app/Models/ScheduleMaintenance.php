<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleMaintenance extends Model
{

    use HasFactory;
    protected $table = 'schedule_maintenances';

    const CREATED_AT = null;
    const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'maintenance_request_id',
        'unit_id',
        'unit_type',
        'maintenance_task',
        'schedule_title',
        'start_date',
        'end_date',
        'status',
        'text_color',
        'bg_color',
        'description',
        'is_reported_issue',
    ];

    public function maintenanceRequest(){
        return $this->belongsTo(MaintenanceRequest::class, 'maintenance_request_id');
    }

    public function unit(){
        return $this->morphTo();
    }
}
