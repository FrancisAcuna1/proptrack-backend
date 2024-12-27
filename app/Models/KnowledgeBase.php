<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeBase extends Model
{
    use HasFactory;
    protected $table = 'knowledge_base';

    const CREATED_AT = true;
    const UPDATED_AT = true;
    public $timestamps = false;

    protected $fillable = [
        'tanong',
        'sagot',
    ];

}
