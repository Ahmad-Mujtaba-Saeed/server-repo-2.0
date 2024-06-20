<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('StudentID');
            $table->string('FatherName');
            $table->string('MotherName');
            $table->string('GuardiansCNIC');
            $table->string('GuardiansPhoneNumber');
            $table->string('GuardiansPhoneNumber2')->nullable();
            $table->string('GuardiansEmail');
            $table->string('HomeAddress')->nullable();
            $table->timestamps();
            $table->foreign('StudentID')
                ->references('id')
                ->on('students')
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
