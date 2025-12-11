<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->foreignId('fund_id')->nullable()->constrained()->onDelete('set null')->after('employee_id');
            
            // Update enum to include 'fund_loan' if your DB supports it, 
            // or just use 'loan' and check fund_id. 
            // For strict databases, we might need a raw statement, but standard logic handles strings fine usually.
        });
    }

    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['fund_id']);
            $table->dropColumn('fund_id');
        });
    }
};