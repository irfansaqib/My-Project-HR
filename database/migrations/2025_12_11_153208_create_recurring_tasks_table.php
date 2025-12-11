<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('recurring_tasks', function (Blueprint $table) {
            $table->id();
            
            // --- STANDARD TASK FIELDS (Template) ---
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_category_id')->constrained()->onDelete('restrict');
            $table->foreignId('assigned_to')->constrained('employees');
            $table->foreignId('created_by')->constrained('users');
            $table->text('description');
            $table->enum('priority', ['Normal', 'Urgent', 'Very Urgent']);
            
            // --- RECURRENCE RULES ---
            $table->enum('frequency', ['Daily', 'Weekly', 'Fortnightly', 'Monthly', 'Quarterly', 'Annually']);
            
            // Logic Fields (Nullable because they depend on frequency)
            $table->time('start_time')->nullable();       // For Daily
            $table->time('end_time')->nullable();         // For Daily
            
            $table->string('day_of_week')->nullable();    // For Weekly (e.g., 'Monday')
            $table->integer('duration_days')->nullable(); // Gap for Weekly/Fortnightly
            
            $table->date('reference_start_date')->nullable(); // For Fortnightly/Quarterly/Annual Anchor
            
            $table->integer('month_start_day')->nullable(); // For Monthly (e.g., 5th)
            $table->integer('month_end_day')->nullable();   // For Monthly (e.g., 10th)
            
            $table->date('annual_start_date')->nullable(); // Store Day/Month for Annual
            $table->date('annual_end_date')->nullable();   // Store Day/Month for Annual

            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->date('last_run_at')->nullable(); // To prevent double creation
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('recurring_tasks');
    }
};