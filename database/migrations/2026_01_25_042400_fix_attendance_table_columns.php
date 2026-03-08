<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // First check what columns exist
        Schema::table('attendances', function (Blueprint $table) {
            // Add total_hours if it doesn't exist
            if (!Schema::hasColumn('attendances', 'total_hours')) {
                $table->decimal('total_hours', 5, 2)->nullable()->after('check_out');
            }
            
            // Add overtime_minutes after total_hours
            if (!Schema::hasColumn('attendances', 'overtime_minutes')) {
                $table->integer('overtime_minutes')->nullable()->after('total_hours');
            }
            
            // Add late_minutes if it doesn't exist
            if (!Schema::hasColumn('attendances', 'late_minutes')) {
                $table->integer('late_minutes')->nullable()->after('is_late');
            }
            
            // Add early_departure_minutes
            if (!Schema::hasColumn('attendances', 'early_departure_minutes')) {
                $table->integer('early_departure_minutes')->nullable()->after('late_minutes');
            }
            
            // Add attendance_type
            if (!Schema::hasColumn('attendances', 'attendance_type')) {
                $table->enum('attendance_type', ['day_wise', 'period_wise'])->default('day_wise')->after('employee_id');
            }
            
            // Add other columns as needed...
        });
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $columns = [
                'total_hours',
                'overtime_minutes', 
                'late_minutes',
                'early_departure_minutes',
                'attendance_type'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('attendances', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};