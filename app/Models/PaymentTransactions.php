<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransactions extends Model
{
    use HasFactory;
    protected $table = 'payment_transactions';

    const CREATED_AT = null;
    const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'rented_unit_id',
        'rented_unit_type',
        'amount',
        'date',
        'paid_for_month',
        'transaction_type',
        'status',
        'months_covered'
 
    ];

    public function tenant(){
        return $this->belongsTo(Account::class, 'tenant_id');
    }

    // public function boardingHouses(){
    //     return $this->belongsTo(BoardingHouse::class, 'rented_unit_id');
    // }

    // public function rentedRoom(){
    //     return $this->belongsTo(BoardingHouse::class, 'room_id');
    // }

    


}
