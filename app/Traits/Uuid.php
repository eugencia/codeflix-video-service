<?php

namespace App\Traits;

use Ramsey\Uuid\Uuid as Guid;

/**
 * Genete hasg (uuid v4)
 */
trait Uuid
{
    public static function boot()
    {
        parent::boot();

        static::creating( function ($model) {
            $model->id = Guid::uuid4();
        });
    }
}
