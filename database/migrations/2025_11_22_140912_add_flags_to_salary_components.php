<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('salary_components', function (Blueprint $table) {
            $table->boolean('is_advance')->default(false);
            $table->boolean('is_loan')->default(false);
        });
    }

    public function down()
    {
        Schema::table('salary_components', function (Blueprint $table) {
            $table->dropColumn(['is_advance', 'is_loan']);
        });
    }
};