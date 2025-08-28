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
        Schema::create('client_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Links to the user who owns this credential
            $table->string('company_name');
            $table->string('user_name');
            $table->string('login_id'); // From your doc's "User ID" field
            $table->string('password');
            $table->string('pin')->nullable();
            $table->string('portal_url');
            $table->text('address')->nullable();
            $table->string('email')->nullable();
            $table->string('company_email')->nullable();
            $table->string('director_email')->nullable();
            $table->string('director_email_password')->nullable();
            $table->string('ceo_name')->nullable();
            $table->string('ceo_cnic')->nullable();
            $table->string('contact_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_credentials');
    }
};