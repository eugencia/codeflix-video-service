<?php

namespace Tests\Feature\Rules;

use App\Models\Category;
use App\Models\Genre;
use App\Rules\GenreHasCategories;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class GenreHasCategoryRuleFeatureTest extends TestCase
{
    use DatabaseMigrations;

    private $categories;

    private $genres;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categories = factory(Category::class, 5)->create();
        $this->genres = factory(Genre::class, 2)->create();

        $this->genres[0]->categories()->sync([
            $this->categories[0]->id,
            $this->categories[1]->id,
            $this->categories[2]->id
        ]);

        $this->genres[1]->categories()->sync([
            $this->categories[2]->id,
            $this->categories[3]->id,
        ]);
    }

    public function testPassesIsValid()
    {
        $rule = new GenreHasCategories([$this->categories[0]->id]);
        $this->assertTrue($rule->passes('', [$this->genres[0]->id]));

        $rule = new GenreHasCategories([$this->categories[1]->id, $this->categories[2]->id]);
        $this->assertTrue($rule->passes('', [$this->genres[0]->id]));

        $rule = new GenreHasCategories([$this->categories[3]->id]);
        $this->assertTrue($rule->passes('', [$this->genres[1]->id]));
    }

    public function testPassesIsInvalid()
    {
        $rule = new GenreHasCategories([$this->categories[4]->id]);
        $this->assertFalse($rule->passes('', [$this->genres[0]->id]));

        $rule = new GenreHasCategories([$this->categories[0]->id]);
        $this->assertFalse($rule->passes('', [$this->genres[0]->id, $this->genres[1]->id]));
    }
}
