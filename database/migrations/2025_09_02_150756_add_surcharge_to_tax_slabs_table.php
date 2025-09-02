<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_slabs', function (Blueprint $table) {
            $table->decimal('surcharge_threshold', 15, 2)->nullable()->after('tax_rate_percentage');
            $table->float('surcharge_rate_percentage')->default(0)->after('surcharge_threshold');
        });
    }

    public function down(): void
    {
        Schema::table('tax_slabs', function (Blueprint $table) {
            $table->dropColumn(['surcharge_threshold', 'surcharge_rate_percentage']);
        });
    }
};