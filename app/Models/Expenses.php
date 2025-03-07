<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    use HasFactory;
    protected $table = 'expenses';

    const CREATED_AT = null;
    const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'unit_id',
        'unit_type',
        'category',
        'type_of_bills',
        'type_of_tax',
        'amount',
        'description',
        'expense_date',
        'recurring',
        'frequency',
        'status'
        // 'lease_end_date'
    ];

    public function unit()
    {
        return $this->morphTo();
    }

    public function expensesImages(){
        return $this->hasMany(MaintenanceImages::class, 'expenses_id');
    }
}
