<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('monthly_salaries')) {
            return;
        }

        Schema::table('monthly_salaries', function (Blueprint $table) {
            // Ensure these columns exist with correct defaults
            if (!Schema::hasColumn('monthly_salaries', 'absent_deduction')) {
                $table->decimal('absent_deduction', 15, 2)->default(0)->after('absent_deduction_amount');
            }
            
            if (!Schema::hasColumn('monthly_salaries', 'manual_fine')) {
                $table->decimal('manual_fine', 15, 2)->default(0)->after('late_deduction');
            }
            
            if (!Schema::hasColumn('monthly_salaries', 'manual_bonus')) {
                $table->decimal('manual_bonus', 15, 2)->default(0)->after('bonus_amount');
            }
            
            if (!Schema::hasColumn('monthly_salaries', 'fine_reason')) {
                $table->text('fine_reason')->nullable()->after('manual_fine');
            }
            
            if (!Schema::hasColumn('monthly_salaries', 'bonus_reason')) {
                $table->text('bonus_reason')->nullable()->after('manual_bonus');
            }
            
            // Add index for faster queries
            $table->index(['employee_id', 'salary_month']);
            $table->index('payment_status');
        });
    }

    public function down()
    {
        Schema::table('monthly_salaries', function (Blueprint $table) {
            $table->dropColumn([
                'absent_deduction',
                'manual_fine',
                'manual_bonus',
                'fine_reason',
                'bonus_reason'
            ]);
            
            $table->dropIndex(['employee_id', 'salary_month']);
            $table->dropIndex(['payment_status']);
        });
    }
};