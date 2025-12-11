<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. The Clients (Companies you serve)
        Schema::create('tax_clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id'); // Your business (Service Provider)
            $table->string('name');
            $table->string('ntn')->nullable();
            $table->string('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        // 2. The Client's Employees (External)
        Schema::create('tax_client_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tax_client_id');
            $table->string('name');
            $table->string('cnic')->nullable();
            $table->string('designation')->nullable();
            $table->date('joining_date')->nullable();
            
            // Current Salary Structure (For next month's auto-calc)
            $table->decimal('current_basic_salary', 15, 2)->default(0);
            $table->json('current_allowances')->nullable(); // Stores {"Medical": 5000, "Fuel": 2000}
            
            // Opening Balances (For Mid-Year Onboarding)
            $table->decimal('opening_taxable_income', 15, 2)->default(0);
            $table->decimal('opening_tax_paid', 15, 2)->default(0);
            
            $table->string('status')->default('active'); // active, resigned
            $table->timestamps();
        });

        // 3. Monthly Sheets
        Schema::create('tax_client_salary_sheets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tax_client_id');
            $table->date('month');
            $table->string('status')->default('draft'); // draft, finalized
            $table->timestamps();
        });

        // 4. Monthly Items (The History)
        Schema::create('tax_client_salary_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salary_sheet_id');
            $table->unsignedBigInteger('tax_client_employee_id');
            
            // Financials for this specific month
            $table->decimal('basic_salary', 15, 2);
            $table->json('allowances_breakdown')->nullable(); // Snapshot of what was paid
            $table->decimal('gross_salary', 15, 2);
            
            // Tax Logic Fields
            $table->decimal('taxable_income_monthly', 15, 2);
            $table->decimal('income_tax', 15, 2);
            $table->decimal('net_salary', 15, 2);
            
            // Reconciliation snapshots (Optional but good for audit)
            $table->decimal('taxable_income_ytd', 15, 2)->default(0);
            $table->decimal('tax_paid_ytd', 15, 2)->default(0);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_client_salary_items');
        Schema::dropIfExists('tax_client_salary_sheets');
        Schema::dropIfExists('tax_client_employees');
        Schema::dropIfExists('tax_clients');
    }
};