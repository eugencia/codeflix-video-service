<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

/**
 * Upload files
 */
trait Uploader
{
    /**
     * Arrat de arquivos antigos
     *
     * @var array
     */
    public $oldFiles = [];

    /**
     * Set the path to save files
     *
     * @return string
     */
    protected abstract function path();

    /**
     * @return void
     */
    public static function bootUploader()
    {
        static::updating(function (Model $model) {

            /** Todos os campos que foram modificados */
            $fieldsUpdated = array_keys($model->getDirty());

            /** Todos os campos de arquivos que foram modificados*/
            $filefieldsUpdated = array_intersect($fieldsUpdated, self::$fileFields);

            /** Filtrar os campos de arquivos que foram atualizados e que tenham algum nome válido */
            $fileFieldsToBeUpdated = Arr::where($filefieldsUpdated, function ($fileField) use ($model) {
                return $model->getOriginal($fileField);
            });

            $model->oldFiles = array_map(function ($fileField) use ($model) {
                return $model->getOriginal($fileField);
            }, $fileFieldsToBeUpdated);
        });
    }

    /**
     * Upload a unique file
     *
     * @param UploadedFile $file
     *
     * @return void
     */
    public function uploadFile(UploadedFile $file)
    {
        $file->store($this->path());
    }

    /**
     * Upload a multiples files
     *
     * @param UploadedFile[] $files
     *
     * @return void
     */
    public function uploadFiles(array $files)
    {
        foreach ($files as $file) {
            $this->uploadFile($file);
        }
    }

    /**
     * Remove a unique file
     *
     * @param UploadedFile|string $file
     *
     * @return void
     */
    public function removeFile($file)
    {
        $fileName = $file instanceof UploadedFile ? $file->hashName() : $file;

        Storage::delete("{$this->path()}/{$fileName}");
    }

    /**
     * Remove a multiples files
     *
     * @param UploadedFile[] $files
     *
     * @return void
     */
    public function removeFiles(array $files)
    {
        foreach ($files as $file) {
            $this->removeFile($file);
        }
    }

    /**
     * Exclui arquivos antigos que foram atualizados
     *
     * @param UploadedFile[] $data
     *
     * @return void
     */
    public function removeOldFiles()
    {
        $this->removeFiles($this->oldFiles);
    }

    /**
     * Retorna o link do arquivo
     *
     * @params  UploadedFile|string $file
     *
     * @return string
     */
    // public function getUrl($file)
    // {
    //     return Storage::url($this->getPath($file));
    // }

    /**
     * Retorna o caminho do arquivo
     *
     * @params  UploadedFile|string $file
     *
     * @return string
     */
    // public function getPath($file)
    // {
    //     if ($file instanceof UploadedFile) {
    //         $file = $file->hashName();
    //     }

    //     return "{$this->path()}/$file";
    // }

    /**
     * Extract attributes files to upload
     *
     * @param array $attributes
     * @var array $uploadedFiles
     * @return array
     */
    public static function extractFiles(array &$attributes = [])
    {
        $uploadedFiles = [];

        foreach (self::$fileFields as $fileField) {

            if (isset($attributes[$fileField]) && $attributes[$fileField] instanceof UploadedFile) {
                $uploadedFiles[] = $attributes[$fileField];

                $attributes[$fileField] = $attributes[$fileField]->hashName();
            }
        }

        return $uploadedFiles;
    }
}
