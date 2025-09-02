<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_slabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('year_from');
            $table->decimal('income_from', 15, 2);
            $table->decimal('income_to', 15, 2)->nullable();
            $table->decimal('fixed_tax_amount', 15, 2)->default(0);
            $table->float('tax_rate_percentage')->default(0); // e.g., 5 for 5%
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_slabs');
    }
};