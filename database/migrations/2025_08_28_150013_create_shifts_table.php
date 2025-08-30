<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->time('punch_in_window_start')->comment('Earliest time a punch is considered for this shift');
            $table->time('punch_in_window_end')->comment('Latest time a punch is considered for this shift');

            // --- ADD THIS LINE ---
            $table->integer('grace_period_in_minutes')->default(0);

            $table->string('weekly_off')->default('Sunday')->comment('e.g., "Sunday", "Saturday,Sunday"');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
