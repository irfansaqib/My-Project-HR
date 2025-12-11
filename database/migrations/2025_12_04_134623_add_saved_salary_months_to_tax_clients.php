<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('tax_clients', function (Blueprint $table) {
        // Adds a JSON column to track months where salary was saved (e.g., ["2025-11", "2025-12"])
        $table->json('saved_salary_months')->nullable()->after('payroll_start_month');
    });
}

public function down()
{
    Schema::table('tax_clients', function (Blueprint $table) {
        $table->dropColumn('saved_salary_months');
    });
}
};
