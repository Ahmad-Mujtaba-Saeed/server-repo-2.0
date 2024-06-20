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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('StudentCNIC');
            $table->unsignedBigInteger('StudentClassID'); // Foreign key referencing classes table
            $table->unsignedBigInteger('StudentUserID'); // Foreign key referencing users table
            $table->string('StudentGender');
            $table->unsignedBigInteger('StudentTeacherID'); // Foreign key referencing teachers table
            $table->date('StudentDOB'); // Use date for DOB
            $table->boolean('StudentAdmissionApproval')->default(false);
            $table->string('StudentPhoneNumber');
            $table->string('StudentHomeAddress');
            $table->decimal('StudentMonthlyFee', 8, 2); // Use decimal for fees
            $table->string('StudentReligion');
            $table->timestamps();



            $table->foreign('StudentUserID')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('StudentClassID')
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
