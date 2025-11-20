<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Column 'type' already exists — safely skipping this migration.
    }

    public function down(): void
    {
        // Nothing to rollback since we skipped it.
    }
};
