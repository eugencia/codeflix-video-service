<?php

namespace Tests\Unit\Models;

use App\Models\Genre;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;
use Tests\Utils\Traits\AssertTheUseOfTraits;

class GenreUnitTest extends TestCase
{
    use AssertTheUseOfTraits;

    public function testIfUseDefaultTraits()
    {
        $traits = [
            Uuid::class,
            SoftDeletes::class
        ];

        $this->assertTheUseOfTraits($traits, Genre::class);
    }
}
