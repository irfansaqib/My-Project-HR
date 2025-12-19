<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('announcements', function (Blueprint $table) {
            // Boolean flag: 0 = Business Only, 1 = Also show on Client Portal
            $table->boolean('is_client_visible')->default(false)->after('type');
        });
    }

    public function down()
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('is_client_visible');
        });
    }
};