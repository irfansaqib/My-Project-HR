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
        Schema::table('employees', function (Blueprint $table) {
            $table->date('exit_date')->nullable()->after('status');
            $table->string('exit_type')->nullable()->after('exit_date'); // e.g., resigned, terminated, retired
            $table->text('exit_reason')->nullable()->after('exit_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['exit_date', 'exit_type', 'exit_reason']);
        });
    }
};