<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_components', function (Blueprint $table) {
            $table->boolean('is_tax_exempt')->default(false)->after('type');
            $table->string('exemption_type')->nullable()->after('is_tax_exempt'); // e.g., 'percentage_of_basic'
            $table->decimal('exemption_value', 10, 2)->nullable()->after('exemption_type'); // e.g., 10 for 10%
        });
    }

    public function down(): void
    {
        Schema::table('salary_components', function (Blueprint $table) {
            $table->dropColumn(['is_tax_exempt', 'exemption_type', 'exemption_value']);
        });
    }
};