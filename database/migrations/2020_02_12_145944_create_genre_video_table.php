<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGenreVideoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('genre_video', function (Blueprint $table) {
            $table->uuid('genre_id')->index();
            $table->uuid('video_id')->index();
            $table->unique(['genre_id', 'video_id']);

            $table->foreign('genre_id')
                ->on('genres')
                ->references('id');
            $table->foreign('video_id')
                ->on('videos')
                ->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('genre_video');
    }
}
