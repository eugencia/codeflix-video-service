<?php

namespace Tests\Feature\Http;

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

    public function testInvalidateRequiredName()
    {
        $data = ['name' => null];

        $this->assertFieldsValidationInCreating($data, 'required');
        $this->assertFieldsValidationInUpdating($data, 'required');
    }

    public function testInvalidateMaxSizeName()
    {
        $data = ['name' => $this->faker()->sentence(256)];

        $this->assertFieldsValidationInCreating($data, 'max.string', ['max' => 255]);
        $this->assertFieldsValidationInUpdating($data, 'max.string', ['max' => 255]);
    }

    public function testInvalidateBooleanIsActive()
    {
        $data = ['is_active' => 'A'];

        $this->assertFieldsValidationInCreating($data, 'boolean');
        $this->assertFieldsValidationInUpdating($data, 'boolean');
    }

    public function testStoreCategoryWithOnlyName()
    {
        $data = ['name' => $this->faker()->name];

        $attributesTestsOnDatabase = $data + ['is_active' => true, 'description' => null, 'deleted_at' => null];
        $attributesTestsOnJsonResponse = $data + ['is_active' => true, 'description' => null, 'deleted_at' => null];

        $this->assertFieldsOnCreate($data, $attributesTestsOnDatabase, $attributesTestsOnJsonResponse);
    }

    public function testStoreCategoryWithSomeDescription()
    {
        $data = ['name' => $this->faker()->name, 'description' => $this->faker()->sentence];

        $this->assertFieldsOnCreate($data, $data + ['deleted_at' => null]);
    }

    public function testStoreCategoryWithStatusInactive()
    {
        $data = ['name' => 'Gênero', 'is_active' => false];

        $this->assertFieldsOnCreate($data, $data + ['is_active' => $data['is_active'], 'deleted_at' => null]);
    }

    public function testUpdateCategoryName()
    {
        $newData = $this->getFakeData(['name' => 'Updated']);

        $this->assertFieldsOnUpdate($newData, $newData + ['deleted_at' => null]);
    }

    public function testUpdateCategoryDescription()
    {
        $newData = $this->getFakeData(['description' => 'Updated']);

        $this->assertFieldsOnUpdate($newData, $newData + ['deleted_at' => null]);
    }

    public function testUpdateCategoryStatus()
    {
        $newData = $this->getFakeData(['is_active' => !$this->category->is_active]);

        $this->assertFieldsOnUpdate($newData, $newData + ['deleted_at' => null]);
    }

    public function testUpdateCategory()
    {
        $data = $this->getFakeData(['name' => 'Updated']);

        $this->assertFieldsOnUpdate($data, $data + ['deleted_at' => null]);

        $data['description'] = 'Updated';

        $this->assertFieldsOnUpdate($data, $data + ['deleted_at' => null]);

        $data['is_active'] = !$this->category->is_active;

        $this->assertFieldsOnUpdate($data, $data + ['deleted_at' => null]);
    }

    public function testDeleteCategory()
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

    private function getFakeData(array $data = [])
    {
        return [
            'name' => $data['name'] ?? $this->faker()->name,
            'description' => $data['description'] ?? $this->faker()->sentence,
            'is_active' => $data['is_active'] ?? $this->faker()->boolean,
        ];
    }
}
