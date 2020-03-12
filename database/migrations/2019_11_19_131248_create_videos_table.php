<?php

use App\Enums\Classification;
use App\Models\Video;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique()->index();
            $table->string('title');
            $table->text('description');
            $table->integer('duration');
            $table->enum('classification', Video::CLASSIFICATION);
            $table->date('release_at');
            $table->string('video_file')->nullable();
            $table->string('banner_file')->nullable();
            $table->string('trailer_file')->nullable();
            $table->string('thumbnail_file')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('videos');
    }
}
