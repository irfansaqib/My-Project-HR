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
        Schema::table('shifts', function (Blueprint $table) {
            // Add the new column after 'grace_period_in_minutes'
            // Defaulting to 60 minutes (1 hour) for existing shifts.
            $table->integer('auto_deduct_minutes')->default(60)->after('grace_period_in_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('auto_deduct_minutes');
        });
    }
};

