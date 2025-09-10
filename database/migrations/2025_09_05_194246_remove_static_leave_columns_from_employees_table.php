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
            $table->dropColumn(['leaves_sick', 'leaves_casual', 'leaves_annual', 'leaves_other']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->integer('leaves_sick')->nullable();
            $table->integer('leaves_casual')->nullable();
            $table->integer('leaves_annual')->nullable();
            $table->integer('leaves_other')->nullable();
        });
    }
};