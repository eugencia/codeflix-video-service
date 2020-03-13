<?php

namespace Tests\Feature\Http;

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
     * @var Video $video
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
            'genres' => ''
        ];

        $this->assertFieldsValidationInCreating($data, 'required');
        $this->assertFieldsValidationInUpdating($data, 'required');
    }

    public function testInvalidateFileFields()
    {
        $this->assertFileInvalidation('video', 'mp4', 12, 'mimetypes', ['values' => 'video/mp4']);
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
    }

    public function testInvalidateDateFormatFields()
    {
        $data = ['release_at' => '31-10-2019'];

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
         * Gêneros e categorias não existem no banco
         */
        $data = ['categories' => [1], 'genres' => [1]];

        $this->assertFieldsValidationInCreating($data, 'exists');
        $this->assertFieldsValidationInUpdating($data, 'exists');

        /**
         * Gêneros e categorias inativas
         */
        $genreInative = factory(Genre::class)->create(['is_active' => false]);
        $categoryInative = factory(Category::class)->create(['is_active' => false]);

        $data = ['categories' => [$categoryInative->id], 'genres' => [$genreInative->id]];

        $this->assertFieldsValidationInCreating($data, 'exists');
        $this->assertFieldsValidationInUpdating($data, 'exists');

        /**
         * Gêneros e categorias excluídas
         */
        $genreActive = factory(Genre::class)->create(['is_active' => true]);
        $categoryActive = factory(Category::class)->create(['is_active' => true]);

        $genreActive->delete();
        $categoryActive->delete();

        $data = ['categories' => [$genreActive->id], 'genres' => [$categoryActive->id]];

        $this->assertFieldsValidationInCreating($data, 'exists');
        $this->assertFieldsValidationInUpdating($data, 'exists');
    }

    public function testIndex()
    {
        $response = $this->json("GET", route('videos.index'));

        $response->assertOk()
            ->assertJson([$this->video->toArray()]);
    }

    public function testShow()
    {
        $response = $this->json("GET", route('videos.show', $this->video->id));

        $response->assertOk()
            ->assertJson($this->video->toArray());
    }

    public function testDelete()
    {
        $response = $this->json('DELETE', route('videos.destroy', $this->video->id));

        $response->assertNoContent();

        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::onlyTrashed()->find($this->video->id));
    }

    public function testSaveWithoutFiles()
    {
        $dataFake = $this->getFakeData() + $this->getFakeRelations();

        $data = [
            [
                'data' => $dataFake,
                'test' => Arr::except($dataFake, ['categories', 'genres'])
            ]
        ];

        foreach ($data as $key => $value) {

            $response =  $response = $this->assertFieldsOnUpdate($value['data'], $value['test']);
            $response->assertJsonStructure([
                'created_at',
                'updated_at'
            ]);
            $this->assertVideoHasCategory($response->json('id'), $value['data']['categories'][0]);
            $this->assertVideoHasGenre($response->json('id'), $value['data']['genres'][0]);

            $response = $this->assertFieldsOnCreate($value['data'], $value['test']);
            $response->assertJsonStructure([
                'created_at',
                'updated_at'
            ]);
            $this->assertVideoHasCategory($response->json('id'), $value['data']['categories'][0]);
            $this->assertVideoHasGenre($response->json('id'), $value['data']['genres'][0]);
        }
    }

    public function testSyncRelations()
    {
        $firstRelations = $this->getFakeRelations();
        $secondRelations = $this->getFakeRelations();

        $data = $this->getFakeData();

        /**
         * Primeiro criar um vídeo
         */
        $response = $this->json("POST", $this->routeStore(), $data + $firstRelations);
        $this->assertVideoHasCategory($response->json('id'), $firstRelations['categories'][0]);
        $this->assertVideoHasGenre($response->json('id'), $firstRelations['genres'][0]);

        /**
         * Atualiza as categorias e gêneros
         */
        $data = $this->getFakeData();
        $response = $this->json("PUT", route('videos.update', $response->json('id')), $data + $secondRelations);
        $this->assertVideoHasCategory($response->json('id'), $secondRelations['categories'][0]);
        $this->assertVideoHasGenre($response->json('id'), $secondRelations['genres'][0]);
        $this->assertDatabaseMissing(
            'category_video',
            [
                'video_id' => $response->json('id'),
                'category_id' => $firstRelations['categories'][0]
            ]
        );
        $this->assertDatabaseMissing(
            'genre_video',
            [
                'video_id' => $response->json('id'),
                'genre_id' => $firstRelations['genres'][0]
            ]
        );
    }

    public function testStoreWithFiles()
    {
        Storage::fake();

        $fakeData = $this->getFakeData();
        $fakeFiles = $this->getFakeFiles();
        $fakeRelations = $this->getFakeRelations();

        $data = $fakeData + $fakeFiles + $fakeRelations;

        $response = $this->json("POST", $this->routeStore(), $data);

        $response->assertCreated();

        $this->assertFilesExists($response->json('id'), $fakeFiles);
    }

    public function testRollbackInStoreWithFiles()
    {
        Storage::fake();

        Event::listen(TransactionCommitted::class, function () {
            throw new TestException;
        });

        $hasError = false;

        try {
            Video::create(
                $this->getFakeData() +
                $this->getFakeFiles() +
                $this->getFakeRelations());
        } catch (\Throwable $th) {
            //throw $th;

            $this->assertCount(0, Storage::allFiles());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testUpdateWithFiles()
    {
        Storage::fake();

        $fakeData = $this->getFakeData();
        $fakeFiles = $this->getFakeFiles();
        $fakeRelations = $this->getFakeRelations();

        $data = $fakeData + $fakeFiles + $fakeRelations;

        $response = $this->json("PUT", $this->routeUpdate(), $data);

        $response->assertOk();

        $this->assertFilesExists($response->json('id'), $fakeFiles);
    }

    private function getFakeFiles()
    {
        return [
            'video' => UploadedFile::fake()->create('video.mp4'),
            'banner' => UploadedFile::fake()->image('banner.jpg'),
            'trailer' => UploadedFile::fake()->create('trailer.mp4'),
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg'),
        ];
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

    private function getFakeRelations(bool $status = true, bool $deleted = false, bool $sync = true)
    {
        $genre = factory(Genre::class)->create(['is_active' => $status]);
        $category = factory(Category::class)->create(['is_active' => $status]);

        if ($deleted) {
            $genre->delete();
            $category->delete();
        }

        if ($sync)
            $genre->categories()->sync($category);

        return [
            'genres' => [$genre->id],
            'categories' => [$category->id],
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
