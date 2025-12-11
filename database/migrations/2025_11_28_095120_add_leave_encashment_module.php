<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add Policy Rules to 'leave_types' table
        Schema::table('leave_types', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_types', 'is_encashable')) {
                $table->boolean('is_encashable')->default(0)->after('name');
                $table->enum('encashment_variable', ['basic_salary', 'gross_salary'])->default('basic_salary')->after('is_encashable');
                $table->integer('encashment_divisor')->default(30)->after('encashment_variable')->comment('Divide salary by this to get 1 day rate');
                $table->integer('min_balance_required')->default(0)->after('encashment_divisor');
                $table->integer('max_days_encashable')->default(0)->after('min_balance_required');
            }
        });

        // 2. Create the Ledger table for Encashment Requests
        if (!Schema::hasTable('leave_encashments')) {
            Schema::create('leave_encashments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('employee_id');
                $table->unsignedBigInteger('leave_type_id');
                $table->unsignedBigInteger('salary_sheet_item_id')->nullable()->comment('Links to payroll when paid');
                $table->decimal('days', 5, 2);
                $table->decimal('amount', 15, 2);
                $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
                $table->date('encashment_date');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 3. Add Encashment Amount column to Payroll Items
        Schema::table('salary_sheet_items', function (Blueprint $table) {
            if (!Schema::hasColumn('salary_sheet_items', 'leave_encashment_amount')) {
                $table->decimal('leave_encashment_amount', 15, 2)->default(0)->after('net_salary');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn(['is_encashable', 'encashment_variable', 'encashment_divisor', 'min_balance_required', 'max_days_encashable']);
        });

        Schema::dropIfExists('leave_encashments');

        Schema::table('salary_sheet_items', function (Blueprint $table) {
            $table->dropColumn('leave_encashment_amount');
        });
    }
};