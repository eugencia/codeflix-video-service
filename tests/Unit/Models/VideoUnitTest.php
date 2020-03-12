<?php

namespace Tests\Unit\Models;

use App\Models\Video;
use App\Traits\Uploader;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\TestCase;
use Tests\Utils\Traits\AssertTheUseOfTraits;

class VideoUnitTest extends TestCase
{
    use AssertTheUseOfTraits;

    public function testIfUseDefaultTraits()
    {
        $traits = [
            Uuid::class,
            Uploader::class,
            SoftDeletes::class,
        ];

        $this->assertTheUseOfTraits($traits, Video::class);
    }
}
