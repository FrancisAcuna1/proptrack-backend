<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasFactory;
    protected $table = 'maintenance_request';

    const CREATED_AT = null;
    const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'reported_issue',
        'other_issue',
        // 'unit_type',
        'issue_description',
        'date_reported',
        'is_schedule',
        'status',
        'urgency_level',
        'remarks',
        'created_at', // Manually handle these
        'updated_at', // Manually handle these
    ];

    public function tenant(){
        return $this->belongsTo(Account::class, 'tenant_id');
    }

    public function maintenanceImages(){
        return $this->hasMany(MaintenanceImages::class, 'maintenance_id');
    }

    public function scheduleMaintenance(){
        return $this->hasOne(ScheduleMaintenance::class, 'maintenance_request_id');
    }
}
