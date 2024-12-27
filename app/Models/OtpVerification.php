<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    use HasFactory;
    protected $table = 'otp_verification_table';

    const CREATED_AT = true;
    const UPDATED_AT = true;
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'otp',
        'expire_at'
    ];
}
