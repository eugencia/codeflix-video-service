<?php

namespace App\Models;

use App\Enums\Classification;
use App\Traits\Uploader;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Video extends Model
{
    use Uuid, SoftDeletes, Uploader;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $with = [
        'genres',
        'categories'
    ];

    protected $fillable = [
        'title',
        'description',
        'classification',
        'duration',
        'release_at',
        'video_file',
        'banner_file',
        'trailer_file',
        'thumbnail_file'
    ];

    public static $fileFields = [
        'video_file',
        'banner_file',
        'trailer_file',
        'thumbnail_file'
    ];

    protected $casts = [
        'classification' => 'int',
        'duration' => 'int',
        'release_at' => 'date_format:Y-m-d'
    ];

    // protected $enumCasts = [
    //     'classification' => Classification::class
    // ];

    public function getVideoFileUrlAttribute()
    {
        if ($this->video_file)
            return $this->video_file;

        return null;
    }

    public function getBannerFileUrlAttribute()
    {
        if ($this->banner_file)
            return $this->banner_file;

        return null;
    }

    public function getTrailerFileUrlAttribute()
    {
        if ($this->trailer_file)
            return $this->trailer_file;

        return null;
    }

    public function getThumbanilFileUrlAttribute()
    {
        if ($this->thumbnail_file)
            return $this->thumbnail_file;

        return null;
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }

    // public static function create(array $attributes = [])
    // {
    //     $fileFields = self::extractFileFields($attributes);

    //     try {
    //         DB::beginTransaction();

    //         $video = static::query()->create($attributes);

    //         $video->syncRelations($attributes);

    //         // $video->upload($fileFields);

    //         DB::commit();

    //         return $video;
    //     } catch (\Throwable $th) {
    //         if (isset($video)) {
    //             // $video->remove($fileFields);
    //         }
    //         DB::rollBack();
    //         throw $th;
    //     }
    // }

    // public function update(array $attributes = [], array $options = [])
    // {
    //     // $fileFields = self::extractFileFields($attributes);

    //     try {
    //         DB::beginTransaction();

    //         $saved = parent::update($attributes, $options);

    //         $this->syncRelations($attributes);

    //         // if ($saved) {
    //         //     $this->upload($fileFields);
    //         // }

    //         DB::commit();

    //         // if ($saved && count($fileFields)) {
    //         //     $this->removeOldFiles();
    //         // }

    //         return $saved;
    //     } catch (\Throwable $th) {
    //         // $this->remove($fileFields);
    //         DB::rollBack();
    //         throw $th;
    //     }
    // }

    // private function syncRelations(array $relations = []): void
    // {
    //     $relations = collect([
    //         'categories' => $relations['categories'] ?? [],
    //         'genres' => $relations['genres'] ?? [],
    //     ]);

    //     if (!empty($relations)) {
    //         $relations->each(function ($values, $key) {
    //             $this->$key()->sync($values);
    //         });
    //     }

    //     $this->fresh();
    // }

    protected function path()
    {
        return $this->id;
    }
}
