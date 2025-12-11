<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // SAFETY CHECK: Only add the column if it does NOT exist yet
        if (!Schema::hasColumn('tax_client_employees', 'manual_tax_deduction')) {
            Schema::table('tax_client_employees', function (Blueprint $table) {
                $table->decimal('manual_tax_deduction', 15, 2)->nullable()->default(null)->after('current_bonus');
            });
        }
    }

    public function down()
    {
        // SAFETY CHECK: Only drop the column if it exists
        if (Schema::hasColumn('tax_client_employees', 'manual_tax_deduction')) {
            Schema::table('tax_client_employees', function (Blueprint $table) {
                $table->dropColumn('manual_tax_deduction');
            });
        }
    }
};