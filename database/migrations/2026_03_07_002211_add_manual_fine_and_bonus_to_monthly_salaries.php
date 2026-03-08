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
        if (! Schema::hasTable('monthly_salaries')) {
            return;
        }

        Schema::table('monthly_salaries', function (Blueprint $table) {
            // Add manual fine column if not exists
            if (!Schema::hasColumn('monthly_salaries', 'manual_fine')) {
                $table->decimal('manual_fine', 15, 2)->default(0)->after('absent_deduction');
            }
            
            // Add fine reason column if not exists
            if (!Schema::hasColumn('monthly_salaries', 'fine_reason')) {
                $table->text('fine_reason')->nullable()->after('manual_fine');
            }
            
            // Add manual bonus column if not exists
            if (!Schema::hasColumn('monthly_salaries', 'manual_bonus')) {
                $table->decimal('manual_bonus', 15, 2)->default(0)->after('bonus_amount');
            }
            
            // Add bonus reason column if not exists
            if (!Schema::hasColumn('monthly_salaries', 'bonus_reason')) {
                $table->text('bonus_reason')->nullable()->after('manual_bonus');
            }
            
            // Add absent_deduction column if not exists (for per-day calculation)
            if (!Schema::hasColumn('monthly_salaries', 'absent_deduction')) {
                $table->decimal('absent_deduction', 15, 2)->default(0)->after('absent_deduction_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_salaries', function (Blueprint $table) {
            $table->dropColumn([
                'manual_fine',
                'fine_reason',
                'manual_bonus',
                'bonus_reason',
                'absent_deduction'
            ]);
        });
    }
};