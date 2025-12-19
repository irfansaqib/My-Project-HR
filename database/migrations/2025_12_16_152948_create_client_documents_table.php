<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_documents', function (Blueprint $table) {
            $table->id();
            // Link to the Client Table
            $table->unsignedBigInteger('client_id');
            // Basic file info
            $table->string('title'); // e.g., "Tax Certificate 2024"
            $table->string('file_path'); // Where the file is saved
            $table->string('file_type')->nullable(); // pdf, jpg, etc.
            $table->string('file_size')->nullable(); 
            $table->text('description')->nullable(); // Optional notes
            $table->timestamps();

            // Foreign key constraint (assumes you have a 'clients' table)
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_documents');
    }
};