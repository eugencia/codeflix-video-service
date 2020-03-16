<?php

namespace Tests\Stubs\Models;

use App\Traits\Uploader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class VideoStub extends Model
{
    use Uploader;

    protected $table = "video_stub";

    protected $fillable = [
        'name',
        'file',
        'image',
    ];

    public static $fileFields = [
        'file',
        'image'
    ];

    protected function path()
    {
        return "1";
    }

    public static function makeTable()
    {
        Schema::create('video_stub', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('file')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public static function dropTable()
    {
        Schema::dropIfExists('video_stub');
    }
}
