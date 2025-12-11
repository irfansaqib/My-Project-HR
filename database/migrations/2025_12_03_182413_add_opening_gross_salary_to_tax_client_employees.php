<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_client_employees', function (Blueprint $table) {
            if (!Schema::hasColumn('tax_client_employees', 'opening_gross_salary')) {
                $table->decimal('opening_gross_salary', 15, 2)->default(0)->after('current_bonus');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tax_client_employees', function (Blueprint $table) {
            $table->dropColumn('opening_gross_salary');
        });
    }
};