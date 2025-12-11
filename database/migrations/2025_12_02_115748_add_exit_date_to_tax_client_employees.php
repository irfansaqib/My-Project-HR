<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_client_employees', function (Blueprint $table) {
            if (!Schema::hasColumn('tax_client_employees', 'exit_date')) {
                $table->date('exit_date')->nullable()->after('joining_date');
            }
            if (!Schema::hasColumn('tax_client_employees', 'status')) {
                $table->string('status')->default('active')->after('exit_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tax_client_employees', function (Blueprint $table) {
            $table->dropColumn(['exit_date', 'status']);
        });
    }
};