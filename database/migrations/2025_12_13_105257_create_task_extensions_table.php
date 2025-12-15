<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('task_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('changed_by')->constrained('users'); // Who extended it?
            $table->dateTime('old_due_date');
            $table->dateTime('new_due_date');
            $table->text('reason')->nullable(); // Why was it extended?
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_extensions');
    }
};