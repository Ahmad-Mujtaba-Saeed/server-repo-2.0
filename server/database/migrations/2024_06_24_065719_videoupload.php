<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('VideoUpload',function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('UsersID');
            // $table->string('VideoCategory');
            $table->string('VideoTitle');
            $table->string('VideoDescription');
            // $table->integer('VideoRank')->nullable();
            $table->unsignedBigInteger('VideoPlaylistID')->nullable();
            $table->string('VideoName');
            $table->date('Date');
            $table->timestamps();
            
            $table->foreign('VideoPlaylistID')
            ->references('id')
            ->on('VideoPlaylist')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
