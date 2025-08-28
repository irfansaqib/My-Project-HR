<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('father_name')->nullable();
            $table->string('cnic')->unique();
            $table->date('dob')->nullable(); // Date of Birth
            $table->string('gender')->nullable();
            $table->string('phone');
            $table->string('email')->unique();
            $table->text('address')->nullable();
            $table->string('designation');
            $table->string('department')->nullable();
            $table->date('joining_date');
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('status')->default('active'); // e.g., active, resigned, terminated
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};