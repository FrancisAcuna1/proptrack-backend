<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileImage extends Model
{
    use HasFactory;
    protected $table = 'profile_image';

    const CREATED_AT = true;
    const UPDATED_AT = true;
    public $timestamps = false;

    protected $fillable = [
        'image_path',
        'tenant_id',
    ];

    public function tenant(){
        return $this->belongsTo(Account::class, 'tenant_id');
    }
}
