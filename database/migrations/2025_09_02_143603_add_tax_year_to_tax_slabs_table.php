<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_slabs', function (Blueprint $table) {
            $table->unsignedInteger('tax_year')->after('business_id');
        });
    }

    public function down(): void
    {
        Schema::table('tax_slabs', function (Blueprint $table) {
            $table->dropColumn('tax_year');
        });
    }
};