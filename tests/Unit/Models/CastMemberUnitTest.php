<?php

namespace Tests\Unit\Models;

use App\Models\CastMember;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\TestCase;
use Tests\Utils\Traits\AssertTheUseOfTraits;

class CastMemberUnitTest extends TestCase
{
    use AssertTheUseOfTraits;

    public function testIfUseDefaultTraits()
    {
        $traits = [
            Uuid::class,
            SoftDeletes::class,
        ];

        $this->assertTheUseOfTraits($traits, CastMember::class);
    }
}
