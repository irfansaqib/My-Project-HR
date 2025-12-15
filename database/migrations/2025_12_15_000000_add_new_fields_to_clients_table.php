<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            // We add the new columns, making them 'nullable' so existing clients don't break
            if (!Schema::hasColumn('clients', 'business_type')) {
                $table->string('business_type')->nullable()->after('business_name');
            }
            if (!Schema::hasColumn('clients', 'cnic')) {
                $table->string('cnic')->nullable()->after('business_type');
            }
            if (!Schema::hasColumn('clients', 'registration_number')) {
                $table->string('registration_number')->nullable()->after('cnic');
            }
            // If you already had 'ntn', this skips it. If not, it adds it.
            if (!Schema::hasColumn('clients', 'ntn')) {
                $table->string('ntn')->nullable()->after('registration_number');
            }
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['business_type', 'cnic', 'registration_number', 'ntn']);
        });
    }
};