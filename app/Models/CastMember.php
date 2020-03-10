<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CastMember extends Model
{
    use Uuid, SoftDeletes;

    const ACTOR = 1;
    const ACTRIZ = 2;
    const DIRECTOR = 3;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'role'
    ];

    protected $casts = [
        'role' => 'int',
    ];
}
