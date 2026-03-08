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
            if (! Schema::hasColumn('monthly_salaries', 'total_allowances')) {
                $table->decimal('total_allowances', 10, 2)->default(0)->after('overtime_amount');
            }

            if (! Schema::hasColumn('monthly_salaries', 'total_deductions')) {
                $table->decimal('total_deductions', 10, 2)->default(0)->after('absent_deduction_amount');
            }

            // ensure other manual fields exist
            if (! Schema::hasColumn('monthly_salaries', 'late_deduction')) {
                $table->decimal('late_deduction', 10, 2)->default(0)->after('late_deduction_amount');
            }

            if (! Schema::hasColumn('monthly_salaries', 'bonus_amount')) {
                $table->decimal('bonus_amount', 10, 2)->default(0)->after('insurance_amount');
            }

            if (! Schema::hasColumn('monthly_salaries', 'insurance_amount')) {
                $table->decimal('insurance_amount', 10, 2)->default(0)->after('absent_deduction_amount');
            }
        });
    }

    public function down()
    {
        Schema::table('monthly_salaries', function (Blueprint $table) {
            if (Schema::hasColumn('monthly_salaries', 'total_allowances')) {
                $table->dropColumn('total_allowances');
            }
            if (Schema::hasColumn('monthly_salaries', 'total_deductions')) {
                $table->dropColumn('total_deductions');
            }
            if (Schema::hasColumn('monthly_salaries', 'late_deduction')) {
                $table->dropColumn('late_deduction');
            }
            if (Schema::hasColumn('monthly_salaries', 'bonus_amount')) {
                $table->dropColumn('bonus_amount');
            }
            if (Schema::hasColumn('monthly_salaries', 'insurance_amount')) {
                $table->dropColumn('insurance_amount');
            }
        });
    }
};