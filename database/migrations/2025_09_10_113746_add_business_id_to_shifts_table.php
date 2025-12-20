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
            // FIX: Only add the column if it does NOT exist
            if (!Schema::hasColumn('shifts', 'business_id')) {
                $table->unsignedBigInteger('business_id')->nullable();
                // If you intended this to be a foreign key, you can uncomment the line below instead:
                // $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            // FIX: Only drop the column if it actually exists
            if (Schema::hasColumn('shifts', 'business_id')) {
                $table->dropColumn('business_id');
            }
        });
    }
};