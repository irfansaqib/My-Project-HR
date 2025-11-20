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
        // ✅ THIS IS THE FIX: Add the code to create the column
        Schema::table('salary_sheet_items', function (Blueprint $table) {
            $table->decimal('bonus', 10, 2)->default(0.00)->after('gross_salary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ✅ Add the rollback code as well
        Schema::table('salary_sheet_items', function (Blueprint $table) {
            $table->dropColumn('bonus');
        });
    }
};