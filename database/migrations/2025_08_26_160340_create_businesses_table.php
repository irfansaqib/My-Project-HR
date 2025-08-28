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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('legal_name');
            $table->string('business_name');
            $table->string('business_type');
            $table->string('ntn_number')->nullable();
            $table->string('registration_number'); // For CNIC / Incorporation Number
            $table->text('address');
            $table->string('phone_number');
            $table->string('email');
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};