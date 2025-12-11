<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Add flag to Salary Components
        Schema::table('salary_components', function (Blueprint $table) {
            $table->boolean('is_contributory')->default(false);
        });

        // 2. The Fund Configuration (e.g., "Provident Fund")
        Schema::create('funds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g. Provident Fund
            
            // Link to the deduction component (Employee Share source)
            $table->foreignId('salary_component_id')->constrained()->onDelete('cascade');
            
            // Employer Contribution Rules
            $table->enum('employer_contribution_type', ['match_employee', 'percentage_of_basic', 'fixed_amount']);
            $table->decimal('employer_contribution_value', 15, 2)->nullable(); // e.g., 10 (for 10%) or 5000 (fixed)
            
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. The Ledger (Tracks money flowing in)
        Schema::create('fund_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fund_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            
            // Link to payroll (optional, as profit distribution won't have a payroll link)
            $table->foreignId('salary_sheet_item_id')->nullable()->constrained()->onDelete('cascade');
            
            $table->enum('type', ['employee_share', 'employer_share', 'profit_credit', 'withdrawal']);
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            
            $table->string('description')->nullable(); // e.g., "Salary Sep 2025" or "Annual Profit 2025-26"
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fund_contributions');
        Schema::dropIfExists('funds');
        Schema::table('salary_components', function (Blueprint $table) {
            $table->dropColumn('is_contributory');
        });
    }
};