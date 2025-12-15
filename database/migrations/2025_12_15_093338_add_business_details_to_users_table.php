<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            
            // 1. Add Business Type
            if (!Schema::hasColumn('users', 'business_type')) {
                $table->string('business_type')->nullable()->after('name');
            }

            // 2. Add CNIC (We define it here since it was missing)
            if (!Schema::hasColumn('users', 'cnic')) {
                $table->string('cnic', 13)->nullable()->unique()->after('password');
            }

            // 3. Add Registration Number
            if (!Schema::hasColumn('users', 'registration_number')) {
                // We place it after password to avoid "column not found" errors
                $table->string('registration_number')->nullable()->unique()->after('password');
            }

            // 4. Add NTN
            if (!Schema::hasColumn('users', 'ntn')) {
                $table->string('ntn')->nullable()->unique()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Safely drop columns if they exist
            $columns = ['business_type', 'cnic', 'registration_number', 'ntn'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};