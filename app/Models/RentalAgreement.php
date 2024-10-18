<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalAgreement extends Model
{
    use HasFactory;
    protected $table = 'rental_agreements';

    const CREATED_AT = null;
    const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'rented_unit_id',
        'rented_unit_type',
        'rental_fee',
        'deposit',
        'lease_start_date',
        'lease_end_date'
    ];

    public function tenant(){
        return $this->belongsTo(Account::class, 'tenant_id');
    }
    
    public function rentedUnit()
    {
        return $this->morphTo();
    }

    public function rentedUnitDetails()
    {
        return $this->hasOne(RentedUnitDetails::class, 'rental_agreement_id');
    }


    


}
