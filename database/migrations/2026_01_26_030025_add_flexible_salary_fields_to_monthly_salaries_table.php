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
        if (!Schema::hasTable('monthly_salaries')) {
            // table not created yet (earlier migration may run later)
            return;
        }

        Schema::table('monthly_salaries', function (Blueprint $table) {
            // Simply add columns without specifying position
            if (!Schema::hasColumn('monthly_salaries', 'custom_components')) {
                $table->json('custom_components')->nullable();
            }
            
            if (!Schema::hasColumn('monthly_salaries', 'attendance_summary')) {
                $table->json('attendance_summary')->nullable();
            }
            
            if (!Schema::hasColumn('monthly_salaries', 'late_deduction_amount')) {
                $table->decimal('late_deduction_amount', 10, 2)->default(0);
            }
            
            if (!Schema::hasColumn('monthly_salaries', 'absent_deduction_amount')) {
                $table->decimal('absent_deduction_amount', 10, 2)->default(0);
            }
            
            if (!Schema::hasColumn('monthly_salaries', 'insurance_amount')) {
                $table->decimal('insurance_amount', 10, 2)->default(0);
            }
            
            if (!Schema::hasColumn('monthly_salaries', 'bonus_amount')) {
                $table->decimal('bonus_amount', 10, 2)->default(0);
            }
            
            if (!Schema::hasColumn('monthly_salaries', 'calculation_details')) {
                $table->json('calculation_details')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_salaries', function (Blueprint $table) {
            $columns = [
                'custom_components',
                'attendance_summary',
                'late_deduction_amount',
                'absent_deduction_amount',
                'insurance_amount',
                'bonus_amount',
                'calculation_details',
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('monthly_salaries', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};