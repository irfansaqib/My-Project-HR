<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This will rename the 'increments' table to 'incentives'.
     */
    public function up(): void
    {
        Schema::rename('increments', 'incentives');
    }

    /**
     * Reverse the migrations.
     * This will rename it back if we ever need to undo the change.
     */
    public function down(): void
    {
        Schema::rename('incentives', 'increments');
    }
};