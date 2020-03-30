<?php

namespace Tests\Feature\Controllers;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Utils\Traits\AssertFields;

class CastMemberControllerFeatureTest extends TestCase
{
    use DatabaseMigrations, WithFaker, AssertFields;

    /**
     * @var CastMember
     */
    private $castMember;

    /**
     * Configuração a ser considerada a cada teste
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->castMember = factory(CastMember::class)->create();
    }

    /**
     * Valida os campos que são obrigatórios
     *
     * @return void
     */
    public function testValidateFields()
    {
        $this->assertFieldsValidationInCreating(['name' => null, 'role' => null], 'required');
        $this->assertFieldsValidationInUpdating(['name' => null, 'role' => null], 'required');

        $this->assertFieldsValidationInCreating(['name' => str_repeat('a', 256)], 'max.string', ['max' => 255]);
        $this->assertFieldsValidationInUpdating(['name' => str_repeat('a', 256)], 'max.string', ['max' => 255]);

        $this->assertFieldsValidationInCreating(['role' => -9], 'in');
        $this->assertFieldsValidationInUpdating(['role' => -9], 'in');
    }

    /**
     * @return void
     */
    public function testIndex()
    {
        $response = $this->json("GET", route('cast-members.index'));

        $response->assertOk()
            ->assertJson([
                'meta' => []
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => array_keys($this->castMember->toArray())
                ],
                'meta' => [],
                'links' => [],
            ]);
    }

    /**
     * Testa a criação de um novo recurso
     *
     * @return void
     */
    public function testStore()
    {
        $data = ['name' => $this->faker()->name, 'role' => CastMember::ACTOR];
        $testsOnResponse = $testOnDatabase = $data + ['deleted_at' => null];
        $this->assertFieldsOnCreate($data, $testOnDatabase, $testsOnResponse);

        $data = ['name' => $this->faker()->name, 'role' => CastMember::DIRECTOR];
        $testsOnResponse = $testOnDatabase = $data + ['deleted_at' => null];
        $this->assertFieldsOnCreate($data, $testOnDatabase, $testsOnResponse);
    }

    /**
     * @return void
     */
    public function testShow()
    {
        $response = $this->json("GET", route('cast-members.show', $this->castMember->id));
        $response->assertOk()
            ->assertJson(['data' => $this->castMember->toArray()])
            ->assertJsonStructure(['data' => [
                'id',
                'name',
                'role',
                'created_at',
                'updated_at',
                'deleted_at',
            ]]);

        $response = $this->json("GET", route('cast-members.show', $this->faker()->uuid));
        $response->assertNotFound();

        $this->castMember->delete();

        $response = $this->json("GET", route('cast-members.show', $this->castMember->id));
        $response->assertNotFound();
    }

    /**
     * @return void
     */
    public function testUpdate()
    {
        $data = ['name' => 'Updated', 'role' => CastMember::DIRECTOR];
        $testsOnResponse = $testOnDatabase = $data + ['deleted_at' => null];
        $this->assertFieldsOnUpdate($data, $testOnDatabase, $testsOnResponse);

        $data = ['name' => 'Updated', 'role' => CastMember::ACTOR];
        $testsOnResponse = $testOnDatabase = $data + ['deleted_at' => null];
        $this->assertFieldsOnUpdate($data, $testOnDatabase, $testsOnResponse);
    }

    /**
     * @return void
     */
    public function testDelete()
    {
        $response = $this->json('DELETE', route('cast-members.destroy', $this->faker()->uuid));
        $response->assertNotFound();
        $this->assertNotNull(CastMember::find($this->castMember->id));
        $this->assertNull(CastMember::onlyTrashed()->find($this->castMember->id));

        $response = $this->json('DELETE', route('cast-members.destroy', $this->castMember->id));
        $response->assertNoContent();
        $this->assertNull(CastMember::find($this->castMember->id));
        $this->assertNotNull(CastMember::onlyTrashed()->find($this->castMember->id));

        $response = $this->json('DELETE', route('cast-members.destroy', $this->castMember)); // ja esta deletado
        $response->assertNotFound();
        $this->assertNull(CastMember::find($this->castMember->id));
        $this->assertNotNull(CastMember::onlyTrashed()->find($this->castMember->id));
    }

    /**
     * Define o model
     */
    protected function model()
    {
        return CastMember::class;
    }

    /**
     * Retorana url para criar um Membro do elenco
     *
     * @return string
     */
    protected function routeStore()
    {
        return route('cast-members.store');
    }

    /**
     * Retorana url para atualizar um Membro do elenco
     *
     * @return string
     */
    protected function routeUpdate()
    {
        return route('cast-members.update', $this->castMember);
    }
}
