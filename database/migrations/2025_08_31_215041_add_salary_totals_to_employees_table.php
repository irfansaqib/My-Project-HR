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
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('basic_salary', 10, 2)->nullable()->after('status');
            $table->decimal('gross_salary', 10, 2)->nullable()->after('basic_salary');
            $table->decimal('net_salary', 10, 2)->nullable()->after('gross_salary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['basic_salary', 'gross_salary', 'net_salary']);
        });
    }
};