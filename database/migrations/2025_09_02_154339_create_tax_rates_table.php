<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('tax_year');
            $table->date('effective_from_date');
            $table->date('effective_to_date')->nullable();
            $table->json('slabs'); // Store all slabs as a JSON array
            $table->decimal('surcharge_threshold', 15, 2)->nullable();
            $table->float('surcharge_rate_percentage')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};