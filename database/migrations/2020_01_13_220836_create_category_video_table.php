<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryVideoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_video', function (Blueprint $table) {
            $table->uuid('category_id')->index();
            $table->uuid('video_id')->index();
            $table->unique(['category_id', 'video_id']);

            $table->foreign('category_id')
                ->on('categories')
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
        Schema::dropIfExists('category_video');
    }
}
