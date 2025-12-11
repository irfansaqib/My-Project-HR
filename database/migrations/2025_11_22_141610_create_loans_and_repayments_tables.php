<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Create 'loans' table only if it doesn't exist
        if (!Schema::hasTable('loans')) {
            Schema::create('loans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained()->onDelete('cascade');
                $table->foreignId('employee_id')->constrained()->onDelete('cascade');
                
                $table->enum('type', ['advance', 'loan']);
                $table->decimal('total_amount', 15, 2);
                
                $table->decimal('installment_amount', 15, 2);
                $table->integer('installments')->default(1);
                
                $table->decimal('recovered_amount', 15, 2)->default(0);
                $table->date('repayment_start_date');
                
                $table->enum('status', ['pending', 'running', 'completed', 'cancelled'])->default('pending');
                $table->text('notes')->nullable();
                
                $table->timestamps();
            });
        }

        // 2. Create 'loan_repayments' table only if it doesn't exist
        if (!Schema::hasTable('loan_repayments')) {
            Schema::create('loan_repayments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('loan_id')->constrained('loans')->onDelete('cascade');
                
                // Nullable: A repayment might be manual (cash) and not linked to a salary sheet
                $table->foreignId('salary_sheet_item_id')->nullable()->constrained('salary_sheet_items')->onDelete('set null');
                
                $table->decimal('amount', 15, 2);
                $table->date('payment_date');
                $table->string('payment_method')->default('salary_deduction'); // 'salary_deduction', 'cash', 'bank_transfer'
                $table->text('notes')->nullable();
                
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('loan_repayments');
        Schema::dropIfExists('loans');
    }
};