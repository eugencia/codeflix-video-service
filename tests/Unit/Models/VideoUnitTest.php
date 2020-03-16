<?php

namespace Tests\Unit\Models;

use App\Models\Video;
use App\Traits\Uploader;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;
use Tests\Utils\Traits\AssertTraits;

class VideoUnitTest extends TestCase
{
    use AssertTraits;

    public function testIfUseDefaultTraits()
    {
        $traits = [
            Uuid::class,
            Uploader::class,
            SoftDeletes::class,
        ];

        $this->assertTraitsUse($traits, Video::class);
    }
}
