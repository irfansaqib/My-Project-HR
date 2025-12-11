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
    Schema::table('tax_client_employees', function (Blueprint $table) {
        if (!Schema::hasColumn('tax_client_employees', 'current_system_tax')) {
            $table->decimal('current_system_tax', 15, 2)->default(0)->after('current_bonus');
            $table->decimal('current_manual_tax', 15, 2)->nullable()->after('current_system_tax');
        }
    });
}

public function down()
{
    Schema::table('tax_client_employees', function (Blueprint $table) {
        $table->dropColumn(['current_system_tax', 'current_manual_tax']);
    });
}
};
