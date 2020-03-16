<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class VideoModelFeatureTest extends TestCase
{
    use DatabaseMigrations, WithFaker;

    /**
     * @var Video
     */
    private $video;

    protected function setUp(): void
    {
        parent::setUp();

        $this->video = new Video;
    }

    public function testCreateWithoutRelations()
    {
        $data = $this->getFakeData();
        Video::create($data);

        $this->assertDatabaseHas('videos', $data);
    }

    public function testCreateWithRelations()
    {
        $data = $this->getFakeData() + $this->getFakeRelations();
        $video = Video::create($data);

        $this->assertDatabaseHas('videos', Arr::except($data, ['categories', 'genres']));
        $this->assertDatabaseHas('category_video', ['video_id' => $video->id, 'category_id' => $data['categories'][0]]);
        $this->assertDatabaseHas('genre_video', ['video_id' => $video->id, 'genre_id' => $data['genres'][0]]);
    }

    public function testCreateWithFiles()
    {
        $data = $this->getFakeData();

        $fileFields = [];

        foreach (Video::$fileFields as $fileField) {
            $fileFields[$fileField] = $fileField . ".test ";
        }

        $video = Video::create($data + $fileFields);

        $this->assertDatabaseHas('videos', $data);
    }

    public function testUpdateWithFiles()
    {
        $video = factory(Video::class)->create();

        $data = $this->getFakeData();

        $fileFields = [];

        foreach (Video::$fileFields as $fileField) {
            $fileFields[$fileField] = $fileField . ".test ";
        }

        $video->update($data + $fileFields);

        $this->assertDatabaseHas('videos', $data + $fileFields);
    }

    public function testUpdateWithoutRelations()
    {
        $video = factory(Video::class)->create();

        $data = $this->getFakeData();

        $video->update($data);

        $this->assertDatabaseHas('videos', $data);
    }

    public function testUpdateWithRelations()
    {
        $video = factory(Video::class)->create();

        $data = $this->getFakeData() + $this->getFakeRelations();

        $video->update($data);

        $this->assertDatabaseHas('videos', Arr::except($data, ['categories', 'genres']));
        $this->assertDatabaseHas('category_video', ['video_id' => $video->id, 'category_id' => $data['categories'][0]]);
        $this->assertDatabaseHas('genre_video', ['video_id' => $video->id, 'genre_id' => $data['genres'][0]]);
    }

    /**
     * Sincronização nula
     */
    public function testSyncRelationsEmpty()
    {
        $video = factory(Video::class)->create();


        Video::syncRelations($video, []);

        $this->assertCount(0, $video->categories);
        $this->assertCount(0, $video->genres);
    }

    /**
     * Sincronização normal
     */
    public function testSyncRelationsNormal()
    {
        $video = factory(Video::class)->create();
        Video::syncRelations($video, $this->getFakeRelations(true, false, true));

        $video->refresh();

        $this->assertCount(1, $video->categories);
        $this->assertCount(1, $video->genres);
    }

    /**
     * Sincronização normal mas com os relacionamentos excluídos
     */
    public function testSyncRelationsDeleted()
    {
        $video = factory(Video::class)->create();
        Video::syncRelations($video, $this->getFakeRelations(true, true, true));

        $video->refresh();

        $this->assertCount(1, $video->categories);
        $this->assertCount(1, $video->genres);
    }

    public function testUpdateRelations()
    {
        $firstRelations = $this->getFakeRelations();
        $secondRelations = $this->getFakeRelations();

        /**
         * @var Video $video
         */
        $video = Video::create($this->getFakeData());

        /**Sincroniza com as primeiras relações */
        Video::syncRelations($video, $firstRelations);
        //Valida a sincronização com as primeiras relações
        $this->assertDatabaseHas('category_video', ['video_id' => $video->id, 'category_id' => $firstRelations['categories'][0]]);
        $this->assertDatabaseHas('genre_video', ['video_id' => $video->id, 'genre_id' => $firstRelations['genres'][0]]);

        /**Atualizar as relações do video com outras */
        Video::syncRelations($video, $secondRelations);
        /**Valida a sincronização com as outras */
        $this->assertDatabaseHas('category_video', ['video_id' => $video->id, 'category_id' => $secondRelations['categories'][0]]);
        $this->assertDatabaseHas('genre_video', ['video_id' => $video->id, 'genre_id' => $secondRelations['genres'][0]]);

        /**Valida a dessincronização com as primeiras */
        $this->assertDatabaseMissing('category_video', ['video_id' => $video->id, 'category_id' => $firstRelations['categories'][0]]);
        $this->assertDatabaseMissing('genre_video', ['video_id' => $video->id, 'genre_id' => $firstRelations['genres'][0]]);
    }

    public function testRollbackCreate()
    {
        $hasError = false;

        try {
            Video::create($this->getFakeData() + ['categories' => [1, 2, 3, 4]]);
        } catch (QueryException $e) {
            $this->assertCount(0, Video::all());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testRollbackUpdate()
    {
        $video = factory(Video::class)->create();
        $oldTitle = $video->title;

        $hasError = false;

        try {
            $video->update($this->getFakeData() + ['categories' => [1, 2, 3, 4]]);
        } catch (QueryException $e) {
            $this->assertDatabaseHas('videos', ['title' => $oldTitle]);
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testGetFIleUrlNullWhenFieldIsNull()
    {
        $video = factory(Video::class)->create();

        foreach(Video::$fileFields as $field){
            $this->assertNull($video->{"{$field}_url"});
        }
    }

    public function testGetFilesUrlWithLocalDriver()
    {
        $fileFields = [];

        foreach (Video::$fileFields as $field) {
            $fileFields[$field] = "{$field}.test";
        }

        $video = factory(Video::class)->create($fileFields);

        $driver = config('filesystems.default');
        $baseUrl = config('filesystems.disks.' . $driver)['url'];

        foreach ($fileFields as $field => $value) {
            $this->assertEquals("{$baseUrl}/{$video->id}/{$value}", $video->{"{$field}_url"});
        }
    }

    // public function testGetFilesUrlWithGCS()
    // {
    //     $fileFields = [];

    //     foreach (Video::$fileFields as $field) {
    //         $fileFields[$field] = "{$field}.test";
    //     }

    //     $video = factory(Video::class)->create($fileFields);

    //     $baseUrl = config('filesystems.disks.gcs.storage_api_uri');

    //     Config::set('filesystems.default', 'gcs');

    //     foreach ($fileFields as $field => $value) {
    //         $this->assertEquals("{$baseUrl}/{$video->id}/{$value}", $video->{"{$field}_url"});
    //     }
    // }

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
}
