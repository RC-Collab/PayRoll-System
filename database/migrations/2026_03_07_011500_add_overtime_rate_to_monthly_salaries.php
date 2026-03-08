<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('monthly_salaries')) {
            return;
        }

        Schema::table('monthly_salaries', function (Blueprint $table) {
            if (!Schema::hasColumn('monthly_salaries', 'overtime_rate')) {
                $table->decimal('overtime_rate', 10, 2)->default(0)->after('overtime_hours');
            }
        });
    }

    public function down(): void
    {
        Schema::table('monthly_salaries', function (Blueprint $table) {
            if (Schema::hasColumn('monthly_salaries', 'overtime_rate')) {
                $table->dropColumn('overtime_rate');
            }
        });
    }
};