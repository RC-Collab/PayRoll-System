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
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        // expand enum values for type column
        DB::statement("ALTER TABLE holidays MODIFY type ENUM('national','regional','company','organization','optional') NOT NULL DEFAULT 'organization'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        // revert to original limited set
        DB::statement("ALTER TABLE holidays MODIFY type ENUM('national','organization','optional') NOT NULL DEFAULT 'organization'");
    }
};
