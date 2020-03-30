<?php

namespace Tests\Feature\Controllers;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Utils\Traits\AssertFields;

class CategoryControllerFeatureTest extends TestCase
{
    use DatabaseMigrations, WithFaker, AssertFields;

    /**
     * @var Category $category
     */
    private $category;

    /**
     * Configuração a ser considerada a cada teste
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->category = factory(Category::class)->create();
    }

    public function testValidateFields()
    {
        $this->assertFieldsValidationInCreating(['name' => null], 'required');
        $this->assertFieldsValidationInUpdating(['name' => null], 'required');

        $this->assertFieldsValidationInCreating(['name' => str_repeat('a', 256)], 'max.string', ['max' => 255]);
        $this->assertFieldsValidationInUpdating(['name' => str_repeat('a', 256)], 'max.string', ['max' => 255]);

        $this->assertFieldsValidationInCreating(['is_active' => 'A'], 'boolean');
        $this->assertFieldsValidationInUpdating(['is_active' => 'A'], 'boolean');
    }

    public function testIndex()
    {
        $response = $this->json("GET", route('categories.index'));

        $response->assertOk()
            ->assertJson([
                'meta' => []
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => array_keys($this->category->toArray())
                ],
                'meta' => [],
                'links' => [],
            ]);
    }

    public function testStore()
    {
        $data = ['name' => $this->faker()->name];
        $testsOnResponse = $testOnDatabase = $data + ['is_active' => true, 'description' => null, 'deleted_at' => null];
        $this->assertFieldsOnCreate($data, $testOnDatabase, $testsOnResponse);

        $data = ['name' => $this->faker()->name, 'description' => $this->faker()->sentence];
        $testsOnResponse = $testOnDatabase = $data + ['is_active' => true, 'deleted_at' => null];
        $this->assertFieldsOnCreate($data, $testOnDatabase, $testsOnResponse);

        $data = ['name' => 'Gênero', 'is_active' => false];
        $testsOnResponse = $testOnDatabase = $data + ['is_active' => false, 'description' => null, 'deleted_at' => null];
        $this->assertFieldsOnCreate($data, $testOnDatabase, $testsOnResponse);
    }

    public function testShow()
    {
        $response = $this->json('GET', route('categories.show', $this->category->id));

        $response->assertOk()
            ->assertJson(['data' => $this->category->toArray()])
            ->assertJsonStructure(['data' => [
                'id',
                'name',
                'description',
                'created_at',
                'updated_at',
                'deleted_at',
            ]]);

        $response = $this->json("GET", route('categories.show', $this->faker()->uuid));
        $response->assertNotFound();

        $this->category->delete();

        $response = $this->json("GET", route('categories.show', $this->category->id));
        $response->assertNotFound();
    }

    public function testUpdate()
    {
        $data = ['name' => 'Updated'];
        $testsOnResponse = $testOnDatabase = $data + ['is_active' => true, 'deleted_at' => null];
        $this->assertFieldsOnUpdate($data, $testOnDatabase, $testsOnResponse);

        $data = ['name' => 'Updated', 'description' => 'Updated'];
        $testsOnResponse = $testOnDatabase = $data + ['is_active' => true, 'deleted_at' => null];
        $this->assertFieldsOnUpdate($data, $testOnDatabase, $testsOnResponse);

        $data = ['name' => 'Updated', 'is_active' => !$this->category->is_active];
        $testsOnResponse = $testOnDatabase = $data + ['is_active' => $data['is_active'], 'deleted_at' => null];
        $this->assertFieldsOnUpdate($data, $testOnDatabase, $testsOnResponse);
    }

    public function testDelete()
    {
        $response = $this->json('DELETE', route('categories.destroy', $this->category->id));
        $response->assertNoContent();

        $this->assertNull(Category::find($this->category->id));
        $this->assertNotNull(Category::onlyTrashed()->find($this->category->id));
    }

    /**
     * Define o model
     */
    protected function model()
    {
        return Category::class;
    }

    /**
     * Retorana url para criar uma categoria
     *
     * @return string
     */
    protected function routeStore()
    {
        return route('categories.store');
    }

    /**
     * Retorana url para atualizar uma categoria específica
     *
     * @return string
     */
    protected function routeUpdate()
    {
        return route('categories.update', $this->category);
    }
}
