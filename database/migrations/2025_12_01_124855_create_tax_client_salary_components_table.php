<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_client_salary_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tax_client_id'); // Linked to the Client, NOT the main business
            $table->string('name'); // e.g., House Rent, Medical
            $table->enum('type', ['allowance', 'deduction']);
            
            // Tax Logic
            $table->boolean('is_tax_exempt')->default(false);
            $table->string('exemption_type')->nullable(); // 'percentage_of_basic', 'fixed_amount'
            $table->decimal('exemption_value', 10, 2)->nullable();
            
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('tax_client_id')->references('id')->on('tax_clients')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_client_salary_components');
    }
};