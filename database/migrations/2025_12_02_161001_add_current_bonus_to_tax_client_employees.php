<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_client_employees', function (Blueprint $table) {
            if (!Schema::hasColumn('tax_client_employees', 'current_bonus')) {
                $table->decimal('current_bonus', 15, 2)->default(0)->after('current_basic_salary');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tax_client_employees', function (Blueprint $table) {
            $table->dropColumn('current_bonus');
        });
    }
};