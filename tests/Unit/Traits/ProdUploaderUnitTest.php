<?php

namespace Tests\Unit\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\Stubs\Models\VideoStub;
use Tests\TestCase;
use Tests\Utils\Traits\AssertStorage;
use Tests\Utils\Traits\AssertTests;

class ProdUploaderUnitTest extends TestCase
{
    use AssertStorage, AssertTests;

    /**
     * @var VideoStub $videoStub
     */
    private $videoStub;

    protected function setUp(): void
    {
        parent::setUp();

        $this->executeTest();

        $this->videoStub =  new VideoStub;

        Config::set('filesystems.default', 'gcs');

        $this->assertStorageDeleteFiles();
    }

    /**
     * Verifica o retorno do caminho relativo do arquivo
     *
     * @return void
     */
    public function testGetRelativeFilePath()
    {
        $this->assertEquals("1/video.mp4", $this->videoStub->getRelativePath('video.mp4'));
    }

    /**
     * Upload single file
     */
    public function testUploadSingleFile()
    {
        $file = UploadedFile::fake()->create("video");

        $this->videoStub->uploadFile($file);
        Storage::assertExists("1/{$file->hashName()}");
    }

    /**
     * Upload multiples files
     */
    public function testUploadMultiplesFiles()
    {
        $files =  [
            UploadedFile::fake()->create("video1"),
            UploadedFile::fake()->create("video2")
        ];

        $this->videoStub->uploadFiles($files);

        Storage::assertExists("1/{$files[0]->hashName()}");
        Storage::assertExists("1/{$files[1]->hashName()}");
    }

    /**
     * Remove single file by object
     */
    public function testRemoveSingleFileByObject()
    {
        $file = UploadedFile::fake()->create("video");

        $this->videoStub->uploadFile($file);
        Storage::assertExists("1/{$file->hashName()}");

        $this->videoStub->removeFile($file);
        Storage::assertMissing("1/{$file->hashName()}");
    }

    /**
     * Remove single file by hash name
     */
    public function testRemoveSingleFileByHasName()
    {
        $file = UploadedFile::fake()->create("video");

        $this->videoStub->uploadFile($file);
        Storage::assertExists("1/{$file->hashName()}");

        $this->videoStub->removeFile($file->hashName());
        Storage::assertMissing("1/{$file->hashName()}");
    }

    /**
     * Remove multiples files by Object
     */
    public function testRemoveMultiplesFilesByObject()
    {
        $files =  [
            UploadedFile::fake()->create("video1"),
            UploadedFile::fake()->create("video2")
        ];

        $this->videoStub->uploadFiles($files);

        Storage::assertExists("1/{$files[0]->hashName()}");
        Storage::assertExists("1/{$files[1]->hashName()}");

        $this->videoStub->removeFiles($files);

        Storage::assertMissing("1/{$files[0]->hashName()}");
        Storage::assertMissing("1/{$files[1]->hashName()}");
    }

    /**
     * Remove multiples files by hash name
     */
    public function testRemoveMultiplesFilesByHashName()
    {
        $files =  [
            UploadedFile::fake()->create("video1"),
            UploadedFile::fake()->create("video2")
        ];

        $this->videoStub->uploadFiles($files);

        Storage::assertExists("1/{$files[0]->hashName()}");
        Storage::assertExists("1/{$files[1]->hashName()}");

        $this->videoStub->removeFiles([
            $files[0]->hashName(),
            $files[1]->hashName()
        ]);

        Storage::assertMissing("1/{$files[0]->hashName()}");
        Storage::assertMissing("1/{$files[1]->hashName()}");
    }

    public function testDeleteOldFiles()
    {
        $fakeFiles = $this->getFakeFiles();

        $this->videoStub->uploadFiles($fakeFiles);

        $this->videoStub->removeOldFiles(); // Remove 0
        $this->assertCount(count($fakeFiles), Storage::allFiles());

        $this->videoStub->oldFiles = [
            $fakeFiles['banner']->hashName()
        ];

        $this->videoStub->removeOldFiles(); // Remove 1
        $this->assertCount(count($fakeFiles) - 1, Storage::allFiles());

        Storage::assertMissing("1/{$fakeFiles['banner']->hashName()}");
        Storage::assertExists("1/{$fakeFiles['thumbnail']->hashName()}");
        Storage::assertExists("1/{$fakeFiles['trailer']->hashName()}");
        Storage::assertExists("1/{$fakeFiles['video']->hashName()}");
    }

    /**
     * Remove multiples files by hash name and Object
     */
    public function testRemoveMultiplesFilesByHashNameAndObject()
    {
        $files =  [
            UploadedFile::fake()->create("video1"),
            UploadedFile::fake()->create("video2")
        ];

        $this->videoStub->uploadFiles($files);

        Storage::assertExists("1/{$files[0]->hashName()}");
        Storage::assertExists("1/{$files[1]->hashName()}");

        $this->videoStub->removeFiles([
            $files[0],
            $files[1]->hashName()
        ]);

        Storage::assertMissing("1/{$files[0]->hashName()}");
        Storage::assertMissing("1/{$files[1]->hashName()}");
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
}
