<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryGenreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_genre', function (Blueprint $table) {
            $table->uuid('category_id')->index();
            $table->uuid('genre_id')->index();
            $table->unique(['category_id', 'genre_id']);

            $table->foreign('category_id')->on('categories')->references('id');
            $table->foreign('genre_id')->on('genres')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_genre');
    }
}
