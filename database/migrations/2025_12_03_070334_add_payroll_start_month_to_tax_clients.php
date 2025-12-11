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
        Schema::table('tax_clients', function (Blueprint $table) {
            // Use hasColumn check to prevent errors if it was partially run
            if (!Schema::hasColumn('tax_clients', 'payroll_start_month')) {
                $table->date('payroll_start_month')->nullable()->after('contact_person');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_clients', function (Blueprint $table) {
            if (Schema::hasColumn('tax_clients', 'payroll_start_month')) {
                $table->dropColumn('payroll_start_month');
            }
        });
    }
};