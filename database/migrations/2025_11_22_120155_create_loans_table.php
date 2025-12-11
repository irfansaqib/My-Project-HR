<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            
            $table->enum('type', ['advance', 'loan']); // Advance = 1 month, Loan = Long term
            $table->decimal('total_amount', 15, 2);
            
            // Repayment details
            $table->integer('installments')->default(1); // 1 for Advance
            $table->decimal('installment_amount', 15, 2); // Amount to cut per month
            $table->decimal('recovered_amount', 15, 2)->default(0);
            
            $table->date('repayment_start_date'); // Which salary to start cutting from
            $table->enum('status', ['pending', 'running', 'completed', 'cancelled'])->default('running');
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });

        // Track every deduction history
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');
            // Nullable because a repayment might be manual (cash), not just via salary sheet
            $table->foreignId('salary_sheet_item_id')->nullable()->constrained()->onDelete('set null'); 
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('loan_repayments');
        Schema::dropIfExists('loans');
    }
};