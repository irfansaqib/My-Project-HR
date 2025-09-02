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
        Schema::table('tax_slabs', function (Blueprint $table) {
            // FIX: Made the new date column nullable to allow migration on existing data
            $table->date('effective_from_date')->nullable()->after('business_id');
            $table->date('effective_to_date')->nullable()->after('effective_from_date');
            $table->dropColumn('year_from');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_slabs', function (Blueprint $table) {
            $table->dropColumn(['effective_from_date', 'effective_to_date']);
            $table->unsignedInteger('year_from');
        });
    }
};