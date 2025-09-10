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
        Schema::create('payroll_salary_sheet_item', function (Blueprint $table) {
            $table->foreignId('payroll_id')->constrained()->onDelete('cascade');
            $table->foreignId('salary_sheet_item_id')->constrained()->onDelete('cascade');
            $table->primary(['payroll_id', 'salary_sheet_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_salary_sheet_item');
    }
};