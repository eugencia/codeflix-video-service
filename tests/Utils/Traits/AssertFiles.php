<?php

namespace Tests\Utils\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait AssertFiles
{
    /**
     * Afirma a existência de arquivo(s)
     *
     * @param Model $model
     * @param UploadedFile|UploadedFile[] $files
     * @return void
     */
    public function assertFilesExists(Model $model, $files)
    {
        if (is_array($files)) {
            foreach ($files as $file) {
                Storage::assertExists($model->getRelativePath($file));
            }

            return;
        }

        Storage::assertExists($model->getRelativePath($files));
    }

    /**
     * Afirma a não existência de arquivo(s)
     *
     * @param Model $model
     * @param UploadedFile|UploadedFile[] $files
     * @return void
     */
    public function assertFilesNotExists(Model $model, $files)
    {

        if (is_array($files)) {
            foreach ($files as $file) {
                Storage::assertMissing($model->getRelativePath($file));
            }

            return;
        }

        Storage::assertMissing($model->getRelativePath($files));
    }
}
