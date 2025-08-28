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
        // Step 1: Rename the column in its own operation
        Schema::table('employees', function (Blueprint $table) {
            $table->renameColumn('salary', 'basic_salary');
        });

        // Step 2: Add the new columns now that the rename is complete
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('house_rent', 10, 2)->nullable()->after('basic_salary');
            $table->decimal('utilities', 10, 2)->nullable()->after('house_rent');
            $table->decimal('medical', 10, 2)->nullable()->after('utilities');
            $table->decimal('conveyance', 10, 2)->nullable()->after('medical');
            $table->decimal('other_allowance', 10, 2)->nullable()->after('conveyance');
            $table->integer('leaves_sick')->default(0)->after('status');
            $table->integer('leaves_casual')->default(0)->after('leaves_sick');
            $table->integer('leaves_annual')->default(0)->after('leaves_casual');
            $table->integer('leaves_other')->default(0)->after('leaves_annual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->renameColumn('basic_salary', 'salary');
            $table->dropColumn([
                'house_rent', 'utilities', 'medical', 'conveyance', 'other_allowance',
                'leaves_sick', 'leaves_casual', 'leaves_annual', 'leaves_other'
            ]);
        });
    }
};