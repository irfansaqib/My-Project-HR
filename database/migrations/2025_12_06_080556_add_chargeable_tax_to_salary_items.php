<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('tax_client_salary_items', function (Blueprint $table) {
        // This stores the System Calculated value (The "150,000")
        $table->decimal('monthly_tax_chargeable', 15, 2)->default(0)->after('taxable_income_monthly');
    });
}

public function down()
{
    Schema::table('tax_client_salary_items', function (Blueprint $table) {
        $table->dropColumn('monthly_tax_chargeable');
    });
}
};
