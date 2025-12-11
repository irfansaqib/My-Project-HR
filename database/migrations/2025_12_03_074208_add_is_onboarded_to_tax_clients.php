<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_clients', function (Blueprint $table) {
            if (!Schema::hasColumn('tax_clients', 'is_onboarded')) {
                $table->boolean('is_onboarded')->default(false)->after('payroll_start_month');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tax_clients', function (Blueprint $table) {
            $table->dropColumn('is_onboarded');
        });
    }
};