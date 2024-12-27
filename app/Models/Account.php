<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Account extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;
    protected $table = 'users';

    const CREATED_AT = null;
    const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'contact',
        'street',
        'barangay',
        'municipality',
        'email',
        'username',
        'password',
        'user_type',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function rentalAgreement(){
        return $this->hasMany(RentalAgreement::class, 'tenant_id');
    }

    public function maintenanceRequest(){
        return $this->hasMany(MaintenanRequest::class, 'tenant_id');
    }

    public function paymentTransactions(){
        return $this->hasMany(PaymentTransactions::class, 'tenant_id');
    }

    public function delequent(){
        return $this->hasMany(Deliquent::class, 'tenant_id');
    }

    public function profileImage(){
        return $this->hasOne(ProfileImage::class, 'tenant_id');
    }
}
