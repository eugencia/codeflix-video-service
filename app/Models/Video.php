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
    use Uuid, Uploader, SoftDeletes;

    const CLASSIFICATION = ['L', 10, 12, 14, 16, 18];

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'description',
        'classification',
        'duration',
        'release_at',
        'video',
        'banner',
        'trailer',
        'thumbnail'
    ];

    /**
     * @var array
     */
    public static $fileFields = [
        'video',
        'banner',
        'trailer',
        'thumbnail'
    ];

    protected $casts = [
        'duration' => 'int',
        'release_at' => 'date_format:Y-m-d'
    ];

    // public function getVideoFileUrlAttribute()
    // {
    //     if ($this->video_file)
    //         return $this->video_file;

    //     return null;
    // }

    // public function getBannerFileUrlAttribute()
    // {
    //     if ($this->banner_file)
    //         return $this->banner_file;

    //     return null;
    // }

    // public function getTrailerFileUrlAttribute()
    // {
    //     if ($this->trailer_file)
    //         return $this->trailer_file;

    //     return null;
    // }

    // public function getThumbanilFileUrlAttribute()
    // {
    //     if ($this->thumbnail_file)
    //         return $this->thumbnail_file;

    //     return null;
    // }

    /**
     * Create a vídeo
     *
     * @param array $attributes
     * @return void
     */
    public static function create(array $attributes = [])
    {
        $files = self::extractFiles($attributes);

        try {
            DB::beginTransaction();

            $video = static::query()->create($attributes);

            static::syncRelations($video, $attributes);

            $video->uploadFiles($files);

            DB::commit();

            return $video;
        } catch (\Throwable $th) {
            if (isset($video)) {
                $video->removeFiles($files);
            }
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Update a vídeo
     *
     * @param array $attributes
     * @param array $options
     * @return void
     */
    public function update(array $attributes = [], array $options = [])
    {
        $files = self::extractFiles($attributes);

        try {
            DB::beginTransaction();

            $saved = parent::update($attributes, $options);

            static::syncRelations($this, $attributes);

            if ($saved) {
                $this->uploadFiles($files);
            }

            DB::commit();

            if ($saved && count($files)) {
                $this->removeOldFiles();
            }

            return $saved;
        } catch (\Throwable $th) {
            $this->removeFiles($files);
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Sincroniza os relacionamentos com o vídeo
     *
     * @param Video $video
     * @param array $relations
     * @return void
     */
    public static function syncRelations(Video $video, array $relations = []): void
    {
        if (isset($relations['categories']))
            $video->categories()->sync($relations['categories']);

        if (isset($relations['genres']))
            $video->genres()->sync($relations['genres']);
    }

    /**
     * Retorna todos as categorias do vídeo, inclusive as excluídas
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTrashed();
    }

    /**
     * Retorna todos os gêneros do vídeo, inclusive os excluídos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function genres()
    {
        return $this->belongsToMany(Genre::class)->withTrashed();
    }

    /**
     * Retorna o ID (uuid) do vídeo como nome do diretório do vídeo
     *
     * @return string
     */
    protected function path()
    {
        return $this->id;
    }
}
