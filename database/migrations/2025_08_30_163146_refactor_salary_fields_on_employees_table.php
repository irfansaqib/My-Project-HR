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
            $table->dropColumn([
                'basic_salary',
                'house_rent',
                'utilities',
                'medical',
                'conveyance',
                'other_allowance',
                'leaves_sick',
                'leaves_casual',
                'leaves_annual',
                'leaves_other',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('basic_salary', 10, 2)->nullable();
            $table->decimal('house_rent', 10, 2)->nullable();
            $table->decimal('utilities', 10, 2)->nullable();
            $table->decimal('medical', 10, 2)->nullable();
            $table->decimal('conveyance', 10, 2)->nullable();
            $table->decimal('other_allowance', 10, 2)->nullable();
            $table->integer('leaves_sick')->nullable();
            $table->integer('leaves_casual')->nullable();
            $table->integer('leaves_annual')->nullable();
            $table->integer('leaves_other')->nullable();
        });
    }
};