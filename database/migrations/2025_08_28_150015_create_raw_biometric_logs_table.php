<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('raw_biometric_logs')) {
            Schema::create('raw_biometric_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id')->nullable();
                $table->timestamp('log_time');
                $table->string('device_id')->nullable();
                $table->string('punch_type')->nullable(); // in / out
                $table->json('raw_payload')->nullable();
                $table->timestamps();

                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_biometric_logs');
    }
};
