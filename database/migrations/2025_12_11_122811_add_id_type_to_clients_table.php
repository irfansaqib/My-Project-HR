<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            // Add 'id_type' before 'ntn_cnic'
            $table->enum('id_type', ['NTN', 'CNIC'])->default('NTN')->after('business_name');
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('id_type');
        });
    }
};