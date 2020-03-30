<?php

namespace Tests\Feature\Controllers;

use App\Models\CastMember;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Utils\Exceptions\TestException;
use Tests\Utils\Traits\AssertFieldsSaves;
use Tests\Utils\Traits\AssertFields;
use Tests\Utils\Traits\AssertFiles;

class VideoControllerFeatureTest extends TestCase
{
    use DatabaseMigrations,
        WithFaker,
        AssertFiles,
        AssertFields;

    /**
     * @var Video
     */
    private $video;

    /**
     * Configuração a ser considerada a cada teste
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->video = factory(Video::class)->create();
    }

    public function testInvalidateRequiredFields()
    {
        $data = [
            'title' => '',
            'description' => '',
            'release_at' => '',
            'duration' => '',
            'classification' => '',
            'categories' => '',
            'genres' => '',
            'cast_members' => ''
        ];

        $this->assertFieldsValidationInCreating($data, 'required');
        $this->assertFieldsValidationInUpdating($data, 'required');
    }

    public function testInvalidateFileFields()
    {

        $fileFields = [
            'video' => [
                'maxSize' => Video::VIDEO_FILE_MAX_SIZE,
                'extension' => 'mp4'
            ],
            'banner' => [
                'maxSize' => Video::BANNER_FILE_MAX_SIZE,
                'extension' => 'jpg'
            ],
            'trailer' => [
                'maxSize' => Video::TRAILER_FILE_MAX_SIZE,
                'extension' => 'mp4'
            ],
            'thumbnail' => [
                'maxSize' => Video::THUMBNAIL_FILE_MAX_SIZE,
                'extension' => 'jpg'
            ]
        ];

        // Tamanho
        foreach ($fileFields as $field => $values) {
            $file = $this->makeFile('test', $values['extension'], $values['maxSize'] + 1);

            $this->assertFieldsValidationInCreating([$field => $file], 'max.file', ['max' => $values['maxSize']]);
            $this->assertFieldsValidationInUpdating([$field => $file], 'max.file', ['max' => $values['maxSize']]);
        }

        // Mimetype
        foreach ($fileFields as $field => $values) {
            $extension = $values['extension'] === 'jpg' ? 'mp4' : 'jpg';
            $mimeType = $extension === 'jpg' ? 'mimetypes' : 'image';
            $params = $mimeType === 'image' ? [] : ['values' => 'video/mp4'];

            $file = $this->makeFile('test', $extension, $values['maxSize']);

            $this->assertFieldsValidationInCreating([$field => $file], $mimeType, $params);
            $this->assertFieldsValidationInUpdating([$field => $file], $mimeType, $params);
        }
    }

    public function testInvalidateMaxSizeFields()
    {
        $data = ['title' => $this->faker()->sentence(256)];

        $this->assertFieldsValidationInCreating($data, 'max.string', ['max' => 255]);
        $this->assertFieldsValidationInUpdating($data, 'max.string', ['max' => 255]);
    }

    public function testInvalidateDateFields()
    {
        $data = ['release_at' => 'sdadsads'];

        $this->assertFieldsValidationInCreating($data, 'date');
        $this->assertFieldsValidationInUpdating($data, 'date');

        $this->assertFieldsValidationInCreating($data, 'date_format', ['format' => 'Y-m-d']);
        $this->assertFieldsValidationInUpdating($data, 'date_format', ['format' => 'Y-m-d']);
    }

    public function testInvalidateIntegerFields()
    {
        $data = ['duration' => 'sdadsads'];

        $this->assertFieldsValidationInCreating($data, 'integer');
        $this->assertFieldsValidationInUpdating($data, 'integer');
    }

    public function testInvalidateEnumFields()
    {
        $data = $this->getFakeData(['classification' => -9]);

        $response = $this->json("POST", $this->routeStore(), $data);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('classification');
    }

    public function testInvalidateArrayFields()
    {
        $data = ['categories' => 'a', 'genres' => 'a'];

        $this->assertFieldsValidationInCreating($data, 'array');
        $this->assertFieldsValidationInUpdating($data, 'array');
    }

    public function testInvalidateExistsFields()
    {
        /**
         * Gêneros, Membro do elenco e categorias não existem no banco
         */
        $this->assertFieldsValidationInCreating(['categories' => [1], 'genres' => [1], 'cast_members' => [1]], 'exists');
        $this->assertFieldsValidationInUpdating(['categories' => [1], 'genres' => [1], 'cast_members' => [1]], 'exists');

        /**
         * Gêneros, categorias inativas
         */
        $this->assertFieldsValidationInCreating(Arr::except($this->getFakeRelations(false, false, false), ['cast_members']), 'exists');
        $this->assertFieldsValidationInUpdating(Arr::except($this->getFakeRelations(false, false, false), ['cast_members']), 'exists');

        /**
         * Gêneros, Membro do elenco e categorias excluídas
         */
        $this->assertFieldsValidationInCreating($this->getFakeRelations(true, true, false), 'exists');
        $this->assertFieldsValidationInUpdating($this->getFakeRelations(true, true, false), 'exists');
    }

    /**
     * @return void
     */
    public function testIndex()
    {
        $response = $this->json("GET", route('videos.index'));

        $response->assertOk()
            ->assertJson([
                'meta' => []
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => array_keys($this->video->toArray())
                ],
                'meta' => [],
                'links' => [],
            ]);
    }

    public function testSaveWithoutFiles()
    {
        $dataFake = $this->getFakeData() + $this->getFakeRelations();

        $data = [
            [
                'data' => $dataFake,
                'test' => Arr::except($dataFake, ['categories', 'genres', 'cast_members']) + ['deleted_at' => null]
            ]
        ];

        foreach ($data as $key => $value) {

            $response = $this->assertFieldsOnCreate($value['data'], $value['test']);
            $this->assertDatabaseHas('genre_video', ['video_id' => $response->json('data.id'), 'genre_id' => $value['data']['genres'][0]]);
            $this->assertDatabaseHas('category_video', ['video_id' => $response->json('data.id'), 'category_id' => $value['data']['categories'][0]]);

            $response = $this->assertFieldsOnUpdate($value['data'], $value['test']);
            $this->assertDatabaseHas('genre_video', ['video_id' => $response->json('data.id'), 'genre_id' => $value['data']['genres'][0]]);
            $this->assertDatabaseHas('category_video', ['video_id' => $response->json('data.id'), 'category_id' => $value['data']['categories'][0]]);
        }
    }
    public function testSaveWithFiles()
    {
        Storage::fake();

        $fakeData = $this->getFakeData();
        $fakeFiles = [
            'video' => $this->makeFile('test', 'mp4'),
            'banner' => $this->makeFile('banner', 'jpg'),
            'trailer' => $this->makeFile('trailer', 'mp4'),
            'thumbnail' => $this->makeFile('thumbnail', 'jpg'),
        ];
        $fakeRelations = $this->getFakeRelations();

        $data = [
            [
                'data' => $fakeData + $fakeFiles + $fakeRelations,
                'test' => $fakeData + ['deleted_at' => null]
            ]
        ];

        foreach ($data as $key => $value) {
            // Store
            $response = $this->assertFieldsOnCreate(
                $value['data'],
                $value['test'],
                $value['test']
            );
            $this->assertFilesExists(
                Video::find($response->json('data.id')),
                $fakeFiles
            );

            // Update
            $newData = $fakeData + $fakeRelations + ['video' => $this->makeFile('other', 'mp4')];

            $response = $this->json(
                "PUT",
                route('videos.update', $response->json('data.id')),
                $newData
            );

            // //Existência dos arquivos antigos
            $this->assertFilesExists(Video::find($response->json('data.id')), [
                $value['data']['trailer'],
                $value['data']['thumbnail'],
                $value['data']['banner'],
            ]);

            //Exclusão do atualizado
            $this->assertFilesNotExists(
                Video::find($response->json('data.id')),
                $value['data']['video']
            );

            // Existência do novo arquivo
            $this->assertFilesExists(Video::find($response->json('data.id')), [
                $newData['video'],
            ]);
        }
    }

    public function testShow()
    {
        $response = $this->json('GET', route('videos.show', $this->video->id));

        $response->assertOk()
            ->assertJson(['data' => $this->video->toArray()])
            ->assertJsonStructure(['data' => [
                'id',
                'title',
                'description',
                'classification',
                'release_at',
                'duration',
                'video',
                'banner',
                'trailer',
                'thumbnail',
                'created_at',
                'updated_at',
                'deleted_at',
            ]]);

        $response = $this->json("GET", route('videos.show', $this->faker()->uuid));
        $response->assertNotFound();

        $this->video->delete();

        $response = $this->json("GET", route('videos.show', $this->video->id));
        $response->assertNotFound();
    }

    public function testDelete()
    {
        $response = $this->json('DELETE', route('videos.destroy', $this->faker()->uuid));
        $response->assertNotFound();
        $this->assertNotNull(Video::find($this->video->id));
        $this->assertNull(Video::onlyTrashed()->find($this->video->id));

        $response = $this->json('DELETE', route('videos.destroy', $this->video->id));
        $response->assertNoContent();
        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::onlyTrashed()->find($this->video->id));

        $response = $this->json('DELETE', route('videos.destroy', $this->video)); // ja esta deletado
        $response->assertNotFound();
        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::onlyTrashed()->find($this->video->id));
    }

    public function testSyncRelations()
    {
        $firstRelations = $this->getFakeRelations();
        $secondRelations = $this->getFakeRelations();

        /**
         * Primeiro criar um vídeo
         */
        $response = $this->json(
            "POST",
            $this->routeStore(),
            $this->getFakeData() + $firstRelations
        );
        $this->assertDatabaseHas(
            'genre_video',
            [
                'video_id' => $response->json('data.id'),
                'genre_id' => $firstRelations['genres'][0]
            ]
        );
        $this->assertDatabaseHas(
            'category_video',
            [
                'video_id' => $response->json('data.id'),
                'category_id' => $firstRelations['categories'][0]
            ]
        );
        $this->assertDatabaseHas(
            'category_video',
            [
                'video_id' => $response->json('data.id'),
                'category_id' => $firstRelations['categories'][0]
            ]
        );

        /**
         * Atualiza os relacionamentos
         */
        $response = $this->json(
            "PUT",
            route('videos.update', $response->json('data.id')),
            $this->getFakeData() + $secondRelations
        );
        $this->assertDatabaseHas(
            'genre_video',
            [
                'video_id' => $response->json('data.id'),
                'genre_id' => $secondRelations['genres'][0]
            ]
        );
        $this->assertDatabaseHas(
            'category_video',
            [
                'video_id' => $response->json('data.id'),
                'category_id' => $secondRelations['categories'][0]
            ]
        );
        // $this->assertDatabaseMissing(
        //     'cast_member_video',
        //     [
        //         'video_id' => $response->json('data.id'),
        //         'cast_member_id' => $firstRelations['cast_members'][0]
        //     ]
        // );
        $this->assertDatabaseMissing(
            'genre_video',
            [
                'video_id' => $response->json('data.id'),
                'genre_id' => $firstRelations['genres'][0]
            ]
        );
    }

    public function testRollbackFilesInStores()
    {
        Storage::fake();

        Event::listen(TransactionCommitted::class, function () {
            throw new TestException;
        });

        $hasError = false;

        try {

            $data = $this->getFakeData() + ['video' => $this->makeFile()] + $this->getFakeRelations();
            Video::create($data);
        } catch (TestException $th) {
            $this->assertCount(0, Storage::allFiles());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testRollbackFilesInUpdate()
    {
        Storage::fake();

        $video = factory(Video::class)->create();

        Event::listen(TransactionCommitted::class, function () {
            throw new TestException;
        });

        $hasError = false;

        try {
            $video->update(
                $this->getFakeData() +
                    ['video' => $this->makeFile()]
            );
        } catch (TestException $th) {
            $this->assertCount(0, Storage::allFiles());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    private function makeFile(
        string $name = 'file',
        string $extension = 'mp4',
        int $size = 0
    ): UploadedFile {
        return UploadedFile::fake()->create("{$name}.{$extension}")->size($size);
    }

    private function getFakeData(array $newData = [])
    {
        $arr = Video::CLASSIFICATION;

        return [
            'title' => $newData['title'] ?? $this->faker()->sentence(2),
            'description' => $newData['description'] ?? $this->faker()->sentence(),
            'duration' => $newData['duration'] ?? $this->faker->randomNumber(2, true),
            'classification' => $newData['classification'] ?? $arr[array_rand($arr)],
            'release_at' => $newData['release_at'] ?? $this->faker->date,
        ];
    }

    private function getFakeRelations(bool $isActive = true, bool $deleted = false, bool $sync = true)
    {
        $genre = factory(Genre::class)->create(['is_active' => $isActive]);
        $category = factory(Category::class)->create(['is_active' => $isActive]);
        $castMember = factory(CastMember::class)->create();

        if ($deleted) {
            $genre->delete();
            $category->delete();
            $castMember->delete();
        }

        if ($sync)
            $genre->categories()->attach($category);

        return [
            'genres' => [$genre->id],
            'categories' => [$category->id],
            'cast_members' => [$castMember->id]
        ];
    }

    protected function assertVideoHasCategory($videoId, $categoryId)
    {
        $this->assertDatabaseHas('category_video', ['video_id' => $videoId, 'category_id' => $categoryId]);
    }

    protected function assertVideoHasGenre($videoId, $genreId)
    {
        $this->assertDatabaseHas('genre_video', ['video_id' => $videoId, 'genre_id' => $genreId]);
    }

    /**
     * Define o model
     */
    protected function model()
    {
        return Video::class;
    }

    /**
     * Retorana url para criar um vídeo
     *
     * @return string
     */
    protected function routeStore()
    {
        return route('videos.store');
    }

    /**
     * Retorana url para atualizar um vídeo específic
     *
     * @return string
     */
    protected function routeUpdate()
    {
        return route('videos.update', $this->video);
    }
}
