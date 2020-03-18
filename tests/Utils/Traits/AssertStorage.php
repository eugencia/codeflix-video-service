<?php

namespace Tests\Utils\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait AssertStorage
{
    /**
     * ARemove todos os arquivos
     *
     * @return void
     */
    protected function assertStorageDeleteFiles()
    {
        $directories = Storage::directories();

        foreach ($directories as $directory) {

            $files = Storage::files($directory);

            Storage::delete($files);

            Storage::deleteDirectory($directory);
        }
    }
}
