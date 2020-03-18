<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCastMemberVideoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cast_member_video', function (Blueprint $table) {
            $table->uuid('cast_member_id')->index();
            $table->uuid('video_id')->index();
            $table->unique(['cast_member_id', 'video_id']);

            $table->foreign('cast_member_id')
                ->on('cast_members')
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
        Schema::dropIfExists('cast_member_video');
    }
}
