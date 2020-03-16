<?php

namespace Tests\Unit\Models;

use Tests\Stubs\Models\VideoStub;
use Tests\TestCase;

class UploaderTraitFeatureTest extends TestCase
{
    /**
     * Instance VideoStub
     *
     * @var VideoStub
     */
    private $videoStub;

    protected function setUp(): void
    {
        parent::setUp();

        $this->videoStub = new VideoStub;

        VideoStub::dropTable();
        VideoStub::makeTable();

    }

    public function testMakeOldFilesOnSave()
    {
        $this->videoStub->fill([
            'name' => 'teste',
            'file' => 'file.mp4',
            'image' => 'image.jpg',
        ]);

        $this->videoStub->save();

        $this->assertCount(0, $this->videoStub->oldFiles);

        $this->videoStub->update([
            'name' => 'teste updated',
            'image' => 'updated'
        ]);

        $this->assertEqualsCanonicalizing(['image.jpg'], $this->videoStub->oldFiles);
    }

    public function testMakeOldFilesOnSaveWithFileFieldsNullable()
    {
        $videoStub = VideoStub::created([
            'name' => 'test'
        ]);

        $this->assertCount(0, $this->videoStub->oldFiles);

        $this->videoStub->update([
            'name' => 'teste updated',
            'image' => 'updated'
        ]);

        $this->assertEquals([], $this->videoStub->oldFiles);
    }
}
