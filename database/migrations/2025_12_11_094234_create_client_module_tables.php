<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. CLIENTS TABLE
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade'); // Link to your main HRMS business
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Login Access
            
            $table->string('business_name');
            $table->string('ntn_cnic')->unique(); // Unique Identifier
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('industry')->nullable();
            $table->text('address')->nullable();
            
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 2. CLIENT ASSIGNMENTS (Linking Employees to Clients)
        Schema::create('client_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            
            // e.g., 'Taxation', 'Audit' - Helps route messages/tasks to the right person
            $table->string('service_type'); 
            $table->foreignId('assigned_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // 3. TASK CATEGORIES (From your PDF: Category -> Sub1 -> Sub2)
        Schema::create('task_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Taxation" or "Sales Tax"
            $table->foreignId('parent_id')->nullable()->constrained('task_categories')->onDelete('cascade');
            $table->integer('level')->default(0); // 0=Main, 1=Sub1, 2=Sub2
            $table->timestamps();
        });

        // 4. TASKS (Compatible with future module)
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_number')->nullable(); // Auto Generated ID
            
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_category_id')->constrained()->onDelete('restrict'); // The specific service
            
            // Who is working on it?
            $table->foreignId('assigned_to')->nullable()->constrained('employees'); 
            // Who created it? (Could be Admin or Client)
            $table->foreignId('created_by')->constrained('users');

            $table->text('description')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('due_date')->nullable();
            
            $table->enum('priority', ['Normal', 'Urgent', 'Very Urgent'])->default('Normal');
            $table->enum('status', ['Pending', 'In Progress', 'Completed', 'Closed'])->default('Pending');
            
            $table->timestamps();
        });
        
        // 5. CLIENT MESSAGES (Chat)
        Schema::create('client_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->nullable()->constrained()->onDelete('cascade'); // Optional link to task
            $table->foreignId('sender_id')->constrained('users');
            $table->foreignId('receiver_id')->nullable()->constrained('users'); // Nullable for group/support msgs
            $table->text('message');
            $table->string('attachment_path')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('client_messages');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('task_categories');
        Schema::dropIfExists('client_assignments');
        Schema::dropIfExists('clients');
    }
};