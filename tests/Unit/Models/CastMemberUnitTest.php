<?php

namespace Tests\Unit\Models;

use App\Models\CastMember;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;
use Tests\Utils\Traits\AssertTraits;

class CastMemberUnitTest extends TestCase
{
    use AssertTraits;

    public function testIfUseDefaultTraits()
    {
        $traits = [
            Uuid::class,
            SoftDeletes::class,
        ];

        $this->assertTraitsUse($traits, CastMember::class);
    }
}
