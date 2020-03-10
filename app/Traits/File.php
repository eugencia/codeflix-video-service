<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

/**
 * Manage files in storage
 */
trait File
{
    protected function deleteAll()
    {
        $directories = Storage::directories();

        foreach ($directories as $directory) {

            $files = Storage::files($directory);

            $deletedFiles = Storage::delete($files);
            $deletedCurrentDirectory = Storage::deleteDirectory($directory);

            // dump("Arquivos do diretório {$deletedCurrentDirectory} deletado: {$deletedFiles}");
        }
    }
}
