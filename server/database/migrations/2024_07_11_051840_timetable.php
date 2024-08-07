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
        Schema::create('timetable', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ClassID');
            $table->string('Subject');
            $table->unsignedBigInteger('TeacherID')->nullable();
            $table->time('StartingTime');
            $table->time('EndingTime');
            $table->timestamps();
            $table->foreign('ClassID')
                ->references('id')
                ->on('classes')
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
