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
            $table->unsignedInteger('leaves_annual')->default(0)->after('job_description');
            $table->unsignedInteger('leaves_sick')->default(0)->after('leaves_annual');
            $table->unsignedInteger('leaves_casual')->default(0)->after('leaves_sick');
            $table->unsignedInteger('leaves_other')->default(0)->after('leaves_casual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['leaves_annual', 'leaves_sick', 'leaves_casual', 'leaves_other']);
        });
    }
};