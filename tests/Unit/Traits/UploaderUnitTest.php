<?php

namespace Tests\Unit\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Stubs\Models\VideoStub;
use Tests\TestCase;

class UploaderUnitTest extends TestCase
{
    /**
     * @var VideoStub $videoStub
     */
    private $videoStub;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake();

        $this->videoStub =  new VideoStub;
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

    public function testExtractFilesEmpty()
    {
        $attributes = [];

        $files = VideoStub::extractFiles($attributes);

        $this->assertCount(0, $attributes);
        $this->assertCount(0, $files);
    }

    public function testExtractSingleFilePassingString()
    {
        $attributes = ['file' => 'file 1'];

        $files = VideoStub::extractFiles($attributes);

        $this->assertCount(1, $attributes);
        $this->assertCount(0, $files);
    }

    public function testExtractMultiplesFilePassingString()
    {
        $attributes = ['file' => 'test 1', 'image' => 'test 2'];

        $files = VideoStub::extractFiles($attributes);

        $this->assertCount(2, $attributes);
        $this->assertCount(0, $files);
    }

    public function testExtractFileFieldsWithZeroStringAttributeAndOneUploadedFileAttribute()
    {
        $file = UploadedFile::fake()->create("firstVideo.mp4");

        $attributes = ['file' => $file];

        $fileFields = VideoStub::extractFiles($attributes);

        $this->assertCount(1, $attributes);
        $this->assertCount(1, $fileFields);
        $this->assertEquals(['file' => $file->hashName()], $attributes);
    }

    public function testExtractFileFieldsWithOneStringAttributeAndOneUploadedFileAttribute()
    {
        $firstFile = UploadedFile::fake()->create("firstVideo.mp4");

        $attributes = ['file' => $firstFile, 'image' => 'test'];

        $fileFields = VideoStub::extractFiles($attributes);

        $this->assertCount(2, $attributes);
        $this->assertCount(1, $fileFields);
        $this->assertEquals([
            'file' => $firstFile->hashName(),
            'image' => 'test'
        ], $attributes);
        $this->assertEquals([$firstFile], $fileFields);
    }

    public function testExtractFileFieldsWithZeroStringAttributeAndMultipleUploadedFileAttribute()
    {
        $firstFile = UploadedFile::fake()->create("firstVideo.mp4");
        $secondFile = UploadedFile::fake()->create("firstVideo.mp4");

        $attributes = ['file' => $firstFile, 'image' => $secondFile];

        $fileFields = VideoStub::extractFiles($attributes);

        $this->assertCount(2, $attributes);
        $this->assertCount(2, $fileFields);
        $this->assertEquals([
            'file' => $firstFile->hashName(),
            'image' => $secondFile->hashName()
        ], $attributes);
        $this->assertEquals([$firstFile, $secondFile], $fileFields);
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
