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
        Schema::create('increments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('effective_date');
            $table->decimal('old_basic_salary', 15, 2);
            $table->decimal('increment_amount', 15, 2);
            $table->decimal('new_basic_salary', 15, 2);
            $table->enum('type', ['increment', 'bonus', 'correction'])->default('increment');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('increments');
    }
};