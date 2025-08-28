<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('degree_title');
            $table->string('institute');
            $table->year('year_of_passing');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qualifications');
    }
};