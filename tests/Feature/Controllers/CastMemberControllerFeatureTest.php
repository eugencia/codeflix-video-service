<?php

namespace Tests\Feature\Http;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Utils\Traits\AssertFields;

class CastMemberControllerFeatureTest extends TestCase
{
    use DatabaseMigrations, WithFaker, AssertFields;

    /**
     * @var CastMember $castMember
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
    public function testInvalidateDataRequired()
    {
        $data = ['name' => null, 'role' => null];

        $this->assertFieldsValidationInCreating($data, 'required');
        $this->assertFieldsValidationInUpdating($data, 'required');
    }

    /**
     * Valida o tamanho máximo da string
     *
     * @return void
     */
    public function testInvalidateDataMaxSize()
    {
        $data = ['name' => str_repeat('a', 256)];

        $this->assertFieldsValidationInCreating($data, 'max.string', ['max' => 255]);
    }

    /**
     * Valida se o role passado é um role conhecido do membro do elenco
     *
     * @return void
     */
    public function testInvalidateDataRole()
    {
        $data = $this->getFakeData(['role' => -9]);

        $response = $this->json('POST', route('cast-members.store'), $data);

        $response->assertJsonValidationErrors(['role']);
    }

    /**
     * Testa o retorno dos recursos
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->json("GET", route('cast-members.index'));

        $response->assertOk()
            ->assertJson([
                $this->castMember->toArray()
            ])
            ->assertJsonCount(1);
    }

     /**
     * Buscando um membro do elenco que não existe
     *
     * @return void
     */
    public function testShowFailsBecauseCastMemberNotExists()
    {
        $this->castMember->delete();

        $response = $this->json("GET", route('cast-members.show', $this->faker()->uuid));

        $response->assertNotFound();
    }

    /**
     * Buscando um membro do elenco excluído
     *
     * @return void
     */
    public function testShowFailsBecauseCastMemberAlreadyDelete()
    {
        $this->castMember->delete();

        $response = $this->json("GET", route('cast-members.show', $this->castMember->id));

        $response->assertNotFound();
    }

     /**
     * Buscando um membro do elenco que ativo
     *
     * @return void
     */
    public function testShowPassesBecauseCastMemberExistsAndIsNotDeleted()
    {
        $response = $this->json("GET", route('cast-members.show', $this->castMember->id));

        $response->assertOk()
            ->assertJson($this->castMember->toArray());
    }

    /**
     * Testa a criação de um novo recurso
     *
     * @return void
     */
    public function testStoreCastMemberWithSomeRole()
    {
        $roleActor = CastMember::ACTOR;

        $data = $this->getFakeData(['role' => $roleActor]);
        $attributesTestOnDatabase = ['role' => $roleActor, 'deleted_at' => null];
        $attributesTestOnJsonResponse = ['name' => $data['name'], 'deleted_at' => null];

        $this->assertFieldsOnCreate($data, $attributesTestOnDatabase, $attributesTestOnJsonResponse);

        $roleDirector = CastMember::DIRECTOR;

        $data = $this->getFakeData(['role' => $roleDirector]);
        $attributesTestOnDatabase = ['role' => $roleDirector, 'deleted_at' => null];
        $attributesTestOnJsonResponse = ['name' => $data['name'], 'deleted_at' => null];

        $this->assertFieldsOnCreate($data, $attributesTestOnDatabase, $attributesTestOnJsonResponse);
    }

    /**
     * Atualizando o nome
     *
     * @return void
     */
    public function testUpdateCastMemberName()
    {
        $newData = $this->getFakeData(['name' => 'Updated']);

        $attributesTestOnDatabase = $newData + ['deleted_at' => null];

        $this->assertFieldsOnUpdate($newData, $attributesTestOnDatabase);
    }

    /**
     * Atualizando o role alguma role válida
     *
     * @return void
     */
    public function testUpdateCastMemberRoleForSomeRole()
    {
        $newRole = CastMember::ACTOR;

        $newData = $this->getFakeData(['role' => $newRole]);

        $attributesTestOnDatabase = $newData + ['deleted_at' => null];

        $this->assertFieldsOnUpdate($newData, $attributesTestOnDatabase);
    }

    /**
     * Membro não encontrado, por não existir no BD
     *
     * @return void
     */
    public function testDeleteFailsBecauseCastMemberNotExists()
    {
        $response = $this->json('DELETE', route('cast-members.destroy', $this->faker()->uuid));

        $response->assertNotFound();

        $this->assertNotNull(CastMember::find($this->castMember->id));
        $this->assertNull(CastMember::onlyTrashed()->find($this->castMember->id));
    }

    /**
     * Membro não encontrado, por já estar excluído
     *
     * @return void
     */
    public function testDeleteFailsBecauseCastMemberIsAlreadyDeleted()
    {
        $this->castMember->delete();

        $response = $this->json('DELETE', route('cast-members.destroy', $this->castMember));

        $response->assertNotFound();

        $this->assertNull(CastMember::find($this->castMember->id));
        $this->assertNotNull(CastMember::onlyTrashed()->find($this->castMember->id));
    }

    /**
     * Cast member excluído
     *
     * @return void
     */
    public function testDeletePassesBecauseCastMemberExistsAndIsNotDeleted()
    {
        $response = $this->json('DELETE', route('cast-members.destroy', $this->castMember->id));

        $response->assertNoContent();

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
     * Retorana url para atualizar um Membro do elenco específic
     *
     * @return string
     */
    protected function routeUpdate()
    {
        return route('cast-members.update', $this->castMember);
    }

    private function getFakeData(array $data = [])
    {
        $arr = [CastMember::ACTOR, CastMember::DIRECTOR];

        return [
            'name' => $data['name'] ?? $this->faker()->name,
            'role' => $data['role'] ?? $arr[array_rand($arr)],
        ];
    }
}
