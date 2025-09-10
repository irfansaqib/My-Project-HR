<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id')->after('id')->nullable();
        });

        if (DB::table('shifts')->count() > 0) {
            $firstBusinessId = DB::table('businesses')->first()->id ?? null;
            if ($firstBusinessId) {
                DB::table('shifts')->whereNull('business_id')->update(['business_id' => $firstBusinessId]);
            }
        }

        Schema::table('shifts', function (Blueprint $table) {
            $table->foreignId('business_id')->change()->constrained()->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropColumn('business_id');
        });
    }
};