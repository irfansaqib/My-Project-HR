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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Essential link to your business user
            $table->string('customer_id')->unique(); // For your custom ID
            $table->string('name');
            $table->string('contact_person')->nullable(); // Suggested addition
            $table->string('email')->nullable();
            $table->string('phone');
            $table->text('address')->nullable();
            $table->string('ntn')->nullable();
            $table->string('cnic')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};