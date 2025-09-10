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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->string('shift_name');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('grace_time_minutes')->default(0)->comment('Grace period for late arrival');

            // New Fields for Punch Window
            $table->time('punch_in_window_start')->comment('Earliest time a punch is considered for this shift');
            $table->time('punch_in_window_end')->comment('Latest time a punch is considered for this shift');
            $table->string('weekly_off_days')->nullable()->comment('Comma-separated days, e.g., "Sunday,Saturday"');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};