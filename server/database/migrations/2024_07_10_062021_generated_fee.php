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
        Schema::create('generatedfee', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('UsersID');
            $table->string('Fee');
            $table->boolean('Paid');
            $table->date('Date');
            $table->string('Role');
            $table->timestamps();
            $table->foreign('UsersID')
                ->references('id')
                ->on('users')
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
