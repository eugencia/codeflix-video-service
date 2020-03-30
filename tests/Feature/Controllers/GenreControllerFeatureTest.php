<?php

namespace Tests\Feature\Controllers;

use App\Http\Controllers\GenreController;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Mockery;
use Tests\TestCase;
use Tests\Utils\Exceptions\TestException;
use Tests\Utils\Traits\AssertFields;

class GenreControllerFeatureTest extends TestCase
{
    use DatabaseMigrations, WithFaker, AssertFields;

    /**
     * @var Genre $genre
     */
    private $genre;

    /**
     * Configuração a ser considerada a cada teste
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->genre = factory(Genre::class)->create(['is_active' => true]);
    }

    public function testValidateFields()
    {
        $data = ['name' => null, 'categories' => null];

        $this->assertFieldsValidationInCreating($data, 'required');
        $this->assertFieldsValidationInUpdating($data, 'required');

        $data = ['name' => $this->faker()->sentence(256)];

        $this->assertFieldsValidationInCreating($data, 'max.string', ['max' => 255]);
        $this->assertFieldsValidationInUpdating($data, 'max.string', ['max' => 255]);

        $data = ['is_active' => 'A'];

        $this->assertFieldsValidationInCreating($data, 'boolean');
        $this->assertFieldsValidationInUpdating($data, 'boolean');

        // Categoria inativa não pode
        $category = factory(Category::class)->create(['is_active' => false]);
        $data = ['categories' => [$category->id]];

        $this->assertFieldsValidationInCreating($data, 'exists');
        $this->assertFieldsValidationInUpdating($data, 'exists');

        //Categoria excluída não pode
        $category = factory(Category::class)->create(['is_active' => true]);
        $category->delete();
        $data = ['categories' => [$category->id]];

        $this->assertFieldsValidationInCreating($data, 'exists');
        $this->assertFieldsValidationInUpdating($data, 'exists');
    }

    public function testIndex()
    {
        $response = $this->json("GET", route('genres.index'));

        $response->assertOk()
            ->assertJson([
                'meta' => []
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => array_keys($this->genre->toArray())
                ],
                'meta' => [],
                'links' => [],
            ]);
    }

    public function testStore()
    {
        $data = $this->getFakeData();

        $testsOnResponse = $testOnDatabase = Arr::except($data, 'categories') + ['deleted_at' => null];
        $response = $this->assertFieldsOnCreate($data, $testOnDatabase, $testsOnResponse);
        $this->assertDatabaseHas('category_genre', ['genre_id' => $response->json('data.id'), 'category_id' => $data['categories'][0]]);
    }

    public function testShow()
    {
        $response = $this->json('GET', route('genres.show', $this->genre->id));

        $response->assertOk()
            ->assertJson(['data' => $this->genre->toArray()])
            ->assertJsonStructure(['data' => [
                'id',
                'name',
                'created_at',
                'updated_at',
                'deleted_at',
            ]]);

        $response = $this->json("GET", route('genres.show', $this->faker()->uuid));
        $response->assertNotFound();

        $this->genre->delete();

        $response = $this->json("GET", route('genres.show', $this->genre->id));
        $response->assertNotFound();
    }

    public function testUpdate()
    {
        $data = $this->getFakeData(['name' => 'updated']);
        $testsOnResponse = $testOnDatabase = Arr::except($data, 'categories') + ['deleted_at' => null];
        $response = $this->assertFieldsOnCreate($data, $testOnDatabase, $testsOnResponse);
        $this->assertDatabaseHas('category_genre', ['genre_id' => $response->json('data.id'), 'category_id' => $data['categories']]);


        $data = $this->getFakeData(['is_active' => !$this->genre->is_active]);
        $testsOnResponse = $testOnDatabase = Arr::except($data, 'categories') + ['deleted_at' => null];
        $response = $this->assertFieldsOnCreate($data, $testOnDatabase, $testsOnResponse);
        $this->assertDatabaseHas('category_genre', ['genre_id' => $response->json('data.id'), 'category_id' => $data['categories']]);
    }


    public function testDelete()
    {
        $response = $this->json('DELETE', route('genres.destroy', $this->faker()->uuid));
        $response->assertNotFound();
        $this->assertNotNull(Genre::find($this->genre->id));
        $this->assertNull(Genre::onlyTrashed()->find($this->genre->id));

        $response = $this->json('DELETE', route('genres.destroy', $this->genre->id));
        $response->assertNoContent();
        $this->assertNull(Genre::find($this->genre->id));
        $this->assertNotNull(Genre::onlyTrashed()->find($this->genre->id));

        $response = $this->json('DELETE', route('genres.destroy', $this->genre)); // ja esta deletado
        $response->assertNotFound();
        $this->assertNull(Genre::find($this->genre->id));
        $this->assertNotNull(Genre::onlyTrashed()->find($this->genre->id));
    }

    public function testSyncRelations()
    {
        $categories = factory(Category::class, 3)
            ->create(['is_active' => true])
            ->modelKeys();

        /**
         * Primeiro criar um gênero com alguma categoria
         */
        $data = ['name' => 'Gênero', 'is_active' => true, 'categories' => [$categories[0]]];
        $response = $this->json("POST", $this->routeStore(), $data);

        $this->assertDatabaseHas('category_genre', ['genre_id' => $response->json('data.id'), 'category_id' => $categories[0]]);

        /**
         * Testar minha sincronização ao atualizar
         */
        $data = ['name' => 'Gênero', 'is_active' => true, 'categories' => [$categories[1], $categories[2]]];
        $response = $this->json("PUT", route('genres.update', $response->json('data.id')), $data);

        $this->assertDatabaseMissing('category_genre', ['genre_id' => $response->json('data.id'), 'category_id' => $categories[0]]);
        $this->assertDatabaseHas('category_genre', ['genre_id' => $response->json('data.id'), 'category_id' => $categories[1]]);
        $this->assertDatabaseHas('category_genre', ['genre_id' => $response->json('data.id'), 'category_id' => $categories[2]]);

        $data = ['name' => 'Gênero', 'is_active' => true, 'categories' => [$categories[0], $categories[2]]];
        $response = $this->json("PUT", route('genres.update', $response->json('data.id')), $data);

        $this->assertDatabaseMissing('category_genre', ['genre_id' => $response->json('data.id'), 'category_id' => $categories[1]]);
        $this->assertDatabaseHas('category_genre', ['genre_id' => $response->json('data.id'), 'category_id' => $categories[0]]);
        $this->assertDatabaseHas('category_genre', ['genre_id' => $response->json('data.id'), 'category_id' => $categories[2]]);
    }

    public function testRollbackStore()
    {
        // Habilitar inclusive os métodos protegidos
        $genreController = Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        // Ignorar o método validate dos dados
        $genreController->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn([
                'name' => $this->faker()->name,
                'categories' => []
            ]);

        // Lançar exceção quando o método syncRelations for chamada
        $genreController->shouldReceive('syncRelations')
            ->once()
            ->andThrow(new TestException);

        $request = Mockery::mock(Request::class);

        $hasError = false;

        try {
            $genreController->store($request);
        } catch (TestException $e) {
            $this->assertCount(1, Genre::all());
            $this->assertCount(0, Genre::with('categories')->first()->categories);
            $hasError = true;
        }

        $this->assertTrue(true);
    }

    public function testRollbackUpdate()
    {
        // Habilitar inclusive os métodos protegidos
        $genreController = Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        //Inabilitar o getModelBy
        $genreController->shouldReceive('getModelBy')
            ->withAnyArgs()
            ->andReturn($this->genre);

        // Ignorar o método validate dos dados
        $genreController->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn([
                'name' => $this->faker()->name
            ]);

        // Lançar exceção quando o método syncRelations for chamada
        $genreController->shouldReceive('syncRelations')
            ->once()
            ->andThrow(new TestException);

        $request = Mockery::mock(Request::class);

        $hasError = false;

        try {
            $genreController->update($request, 1);
        } catch (TestException $e) {
            // 1 no setup + 1 ao executar o syncRelations
            $this->assertCount(1, Genre::all());
            $this->assertCount(0, Genre::with('categories')->first()->categories);
            $hasError = true;
        }

        $this->assertTrue(true);
    }

    /**
     * Define o model
     */
    protected function model()
    {
        return Genre::class;
    }

    /**
     * Retorana url para criar um gênero
     *
     * @return string
     */
    protected function routeStore()
    {
        return route('genres.store');
    }

    /**
     * Retorana url para atualizar um gênero específico
     *
     * @return string
     */
    protected function routeUpdate()
    {
        return route('genres.update', $this->genre);
    }

    private function getFakeData(array $data = [])
    {
        $category = factory(Category::class)->create(['is_active' => true]);

        return [
            'name' => $data['name'] ?? $this->faker()->name,
            'is_active' => $data['is_active'] ?? $this->faker()->boolean,
            'categories' => $data['categories'] ?? [$category->id]
        ];
    }
}
