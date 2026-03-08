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
        // drop the legacy 'organization' option from the enum
        DB::statement("ALTER TABLE holidays MODIFY type ENUM('national','regional','company','optional') NOT NULL DEFAULT 'national'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        // re-add the 'organization' value if rolling back
        DB::statement("ALTER TABLE holidays MODIFY type ENUM('national','regional','company','organization','optional') NOT NULL DEFAULT 'national'");
    }
};
