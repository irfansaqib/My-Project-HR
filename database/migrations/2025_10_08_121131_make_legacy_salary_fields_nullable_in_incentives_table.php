<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This makes old increment-related columns nullable in the repurposed incentives table.
     */
    public function up(): void
    {
        Schema::table('incentives', function (Blueprint $table) {
            // These columns are from the old 'increments' functionality and are not
            // needed for bonuses. We make them nullable to prevent errors on insert.
            $table->decimal('old_basic_salary', 15, 2)->nullable()->change();
            $table->decimal('new_basic_salary', 15, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incentives', function (Blueprint $table) {
            // This reverts the change if needed.
            $table->decimal('old_basic_salary', 15, 2)->nullable(false)->change();
            $table->decimal('new_basic_salary', 15, 2)->nullable(false)->change();
        });
    }
};