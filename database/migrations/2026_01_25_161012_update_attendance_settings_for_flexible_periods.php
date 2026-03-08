<?php
// database/migrations/[timestamp]_update_attendance_settings_for_flexible_periods.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check and add columns if they don't exist
        Schema::table('attendance_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_settings', 'total_periods')) {
                $table->integer('total_periods')->default(8)->nullable()->after('is_period_based');
            }
            
            if (!Schema::hasColumn('attendance_settings', 'first_period_start')) {
                $table->time('first_period_start')->nullable()->after('total_periods');
            }
            
            if (!Schema::hasColumn('attendance_settings', 'first_period_end')) {
                $table->time('first_period_end')->nullable()->after('first_period_start');
            }
            
            if (!Schema::hasColumn('attendance_settings', 'period_schedule')) {
                $table->json('period_schedule')->nullable()->after('first_period_end');
            }
            
            if (!Schema::hasColumn('attendance_settings', 'enable_overtime_periods')) {
                $table->boolean('enable_overtime_periods')->default(false)->after('period_schedule');
            }
            
            if (!Schema::hasColumn('attendance_settings', 'max_overtime_periods')) {
                $table->integer('max_overtime_periods')->default(2)->nullable()->after('enable_overtime_periods');
            }
            
            if (!Schema::hasColumn('attendance_settings', 'flexible_schedule')) {
                $table->boolean('flexible_schedule')->default(false)->after('max_overtime_periods');
            }
            
            if (!Schema::hasColumn('attendance_settings', 'custom_schedule')) {
                $table->json('custom_schedule')->nullable()->after('flexible_schedule');
            }
        });
    }

    public function down()
    {
        // We'll only remove columns if they exist and we want to rollback
        Schema::table('attendance_settings', function (Blueprint $table) {
            $columnsToDrop = [
                'total_periods',
                'first_period_start',
                'first_period_end',
                'period_schedule',
                'enable_overtime_periods',
                'max_overtime_periods',
                'flexible_schedule',
                'custom_schedule'
            ];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('attendance_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};