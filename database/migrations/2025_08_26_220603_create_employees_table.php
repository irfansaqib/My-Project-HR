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

            // Consolidated Columns
            $table->string('employee_number')->unique()->nullable();
            
            // Your original controller uses a single 'name' field, so we'll match that
            $table->string('name');
            $table->string('father_name')->nullable();
            
            // === ADDED THE MISSING CNIC COLUMN HERE ===
            $table->string('cnic')->unique();

            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->date('joining_date')->nullable();
            $table->text('address')->nullable();
            $table->string('status')->default('active');
            
            // Employment Details
            $table->string('designation')->nullable();
            $table->string('department')->nullable();

            // Salary Details
            $table->decimal('basic_salary', 10, 2)->nullable();
            $table->decimal('house_rent', 10, 2)->nullable();
            $table->decimal('utilities', 10, 2)->nullable();
            $table->decimal('medical', 10, 2)->nullable();
            $table->decimal('conveyance', 10, 2)->nullable();
            $table->decimal('other_allowance', 10, 2)->nullable();

            // Leave Details
            $table->integer('leaves_sick')->nullable();
            $table->integer('leaves_casual')->nullable();
            $table->integer('leaves_annual')->nullable();
            $table->integer('leaves_other')->nullable();
            $table->date('leave_period_from')->nullable();
            $table->date('leave_period_to')->nullable();

            // Emergency Contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_relation')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            
            // File Paths
            $table->string('photo_path')->nullable();
            $table->string('attachment_path')->nullable();
            
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