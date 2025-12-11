<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_sheet_items', function (Blueprint $table) {
            // 1. Arrears (Added AFTER Net Salary)
            $table->decimal('arrears_adjustment', 15, 2)->default(0)->after('net_salary');
            
            // 2. Final Payable (Net Salary + Arrears)
            $table->decimal('payable_amount', 15, 2)->default(0)->after('arrears_adjustment');
            
            // 3. Payment Tracking
            $table->decimal('paid_amount', 15, 2)->default(0)->after('payable_amount');
            
            // 4. Status Tracking
            // We update the existing status or add a new specific payment status
            // Let's add a specific payment_status to be safe
            $table->enum('payment_status', ['unpaid', 'held', 'partial', 'paid'])->default('unpaid')->after('status');
            
            // 5. Track Manual Edits
            $table->boolean('is_tax_manual')->default(false)->after('income_tax');
        });
    }

    public function down(): void
    {
        Schema::table('salary_sheet_items', function (Blueprint $table) {
            $table->dropColumn(['arrears_adjustment', 'payable_amount', 'paid_amount', 'payment_status', 'is_tax_manual']);
        });
    }
};