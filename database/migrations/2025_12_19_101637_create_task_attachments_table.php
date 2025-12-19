<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('task_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id'); // Link to Task
            $table->string('file_name')->nullable();
            $table->string('file_path');
            $table->string('file_type')->nullable(); // pdf, jpg, etc
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            // Foreign Key Constraint (Optional but recommended)
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_attachments');
    }
};