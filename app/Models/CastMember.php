<?php

namespace App\Models;

use App\Enums\Role;
use App\Traits\Uuid;
use BenSampo\Enum\Traits\CastsEnums;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CastMember extends Model
{
    use Uuid, SoftDeletes;

    const ACTOR = 1;
    const DIRECTOR = 2;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'role'
    ];

    protected $casts = [
        'role' => 'integer'
    ];
}
