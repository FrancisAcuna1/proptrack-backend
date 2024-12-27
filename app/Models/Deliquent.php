<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deliquent extends Model
{
    use HasFactory;
    protected $table = 'delequent';

    const CREATED_AT = null;
    const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'amount_overdue',
        'month_overdue',
        'status'
    ];

    public function tenant(){
        return $this->belongsTo(Account::class, 'tenant_id');
    }

}
