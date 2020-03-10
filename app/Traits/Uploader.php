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
    public $oldFiles = [];

    protected abstract function path();

    /**
     *
     * @return void
     */
    public static function bootUpload()
    {
        static::updating(function (Model $model) {

            $allFieldsChanged = array_keys($model->getDirty());
            $allFileFieldsChanged = array_intersect($allFieldsChanged, self::$fileFields);

            $fileFieldsToBeUpdated = Arr::where($allFileFieldsChanged, function ($fileField) use ($model) {
                return $model->getOriginal($fileField);
            });

            $model->oldFiles = array_map(function ($fileField) use ($model) {
                return $model->getOriginal($fileField);
            }, $fileFieldsToBeUpdated);
        });
    }

    /**
     * Upload file
     *
     * @param UploadedFile|array $data
     *
     * @return void
     */
    public function upload($data)
    {
        if (is_array($data)) {
            foreach ($data as $file) {
                $this->saveOnStorage($file);
            }

            return;
        }

        $this->saveOnStorage($data);
    }

    /**
     * Upload file
     *
     * @param array|UploadedFile $data
     *
     * @return void
     */
    public function remove($data)
    {
        if (is_array($data)) {
            foreach ($data as $file) {
                $this->removeOnStorage($file);
            }

            return;
        }

        $this->removeOnStorage($data);
    }

    /**
     * Remove old files
     *
     * @param UploadedFile[] $data
     *
     * @return void
     */
    public function removeOldFiles()
    {
        // dump($this->oldFiles);
        
        $this->remove($this->oldFiles);
    }

    /**
     * Retorna o link do arquivo
     * 
     * @params  UploadedFile|string $file
     * 
     * @return string
     */
    public function getUrl($file)
    {
        return Storage::url($this->getPath($file));
    }

    /**
     * Retorna o caminho do arquivo
     * 
     * @params  UploadedFile|string $file
     * 
     * @return string
     */
    public function getPath($file)
    {
        if ($file instanceof UploadedFile) {
            $file = $file->hashName();
        }

        return "{$this->path()}/$file";
    }

    public static function extractFileFields(array &$attributes = [])
    {
        $files = [];

        foreach (self::$fileFields as $fileField) {

            if (isset($attributes[$fileField]) && $attributes[$fileField] instanceof UploadedFile) {
                $files[] = $attributes[$fileField];

                $attributes[$fileField] = $attributes[$fileField]->hashName();
            }
        }

        return $files;
    }

    /**
     * Save a file in storage
     *
     * @param UploadedFile $file
     * @return void
     */
    private function saveOnStorage(UploadedFile $file)
    {
        $file->store($this->path());
    }

    /**
     * Remove a file in storage
     *
     * @param string|UploadedFile $file
     * @return void
     */
    private function removeOnStorage($file)
    {
        $fileName = $file instanceof UploadedFile ? $file->hashName() : $file;

        Storage::delete("{$this->path()}/{$fileName}");
    }
}
