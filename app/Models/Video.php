<?php

namespace App\Models;

use App\Enums\Classification;
use App\Filters\VideoFilter;
use App\Traits\Uploader;
use App\Traits\Uuid;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Video extends Model
{
    use Uuid, Uploader, Filterable, SoftDeletes;

    const CLASSIFICATION = ['L', '10', '12', '14', '16', '18'];

    const VIDEO_FILE_MAX_SIZE = 1024 * 5; // 5MB
    const BANNER_FILE_MAX_SIZE = 1024 * 10; // 10MB
    const TRAILER_FILE_MAX_SIZE = 1024 * 1024 * 1; // 1 GB
    const THUMBNAIL_FILE_MAX_SIZE = 1024 * 1024 * 50; // 50 GB

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @var array
     */
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

    /**
     * @var array
     */
    protected $casts = [
        'id' => 'string',
        'duration' => 'integer',
        'classification' => 'string',
        'release_at' => 'date_format:Y-m-d'
    ];

    /**
     * Cria um novo vídeo
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
     * Atualiza informações de um vídeo
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

    public function modelFilter()
    {
        return $this->provideFilter(VideoFilter::class);
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

        if (isset($relations['cast_members']))
            $video->castMembers()->sync($relations['cast_members']);
    }

    /**
     * Gerar a url para o video
     *
     * @return string|null
     */
    public function getVideoUrlAttribute()
    {
        if ($this->video)
            return $this->getUrl($this->video);

        return null;
    }

    /**
     * Gerar a url para o banner
     *
     * @return string|null
     */
    public function getBannerUrlAttribute()
    {
        if ($this->banner)
            return $this->getUrl($this->banner);

        return null;
    }

    /**
     * Gerar a url para o trailer
     *
     * @return string|null
     */
    public function getTrailerUrlAttribute()
    {
        if ($this->trailer)
            return $this->getUrl($this->trailer);

        return null;
    }

    /**
     * Gerar a url para o thumbnail
     *
     * @return string|null
     */
    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail)
            return $this->getUrl($this->thumbnail);

        return null;
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
     * Retorna todos os gêneros do vídeo, inclusive os excluídos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function castMembers()
    {
        return $this->belongsToMany(CastMember::class)->withTrashed();
    }

    /**
     * Retorna o nome do diretório do vídeo
     *
     * @return string
     */
    protected function path()
    {
        return $this->id;
    }
}
