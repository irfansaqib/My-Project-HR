<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->date('loan_date')->nullable()->after('employee_id');
        });

        // Auto-fill existing records with their creation date so the field isn't empty
        DB::statement('UPDATE loans SET loan_date = DATE(created_at)');
    }

    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('loan_date');
        });
    }
};