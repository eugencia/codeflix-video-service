<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\TestCase;
use Tests\Utils\Traits\AssertTraits;

class CategoryUnitTest extends TestCase
{
    use AssertTraits;

    public function testIfUseDefaultTraits()
    {
        $traits = [
            Uuid::class,
            SoftDeletes::class
        ];

        $this->assertTraitsUse($traits, Category::class);
    }
}
