<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. New Table for Time Tracking
        Schema::create('task_time_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Who worked on it
            $table->dateTime('started_at');
            $table->dateTime('stopped_at')->nullable();
            $table->integer('duration_minutes')->default(0); // Calculated on stop
            $table->timestamps();
        });

        // 2. Add Supervisor & Workflow columns to Tasks
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->after('assigned_to');
            $table->dateTime('executed_at')->nullable(); // When employee marks as done
            $table->dateTime('completed_at')->nullable(); // When supervisor accepts
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_time_logs');
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn(['supervisor_id', 'executed_at', 'completed_at']);
        });
    }
};