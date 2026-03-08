<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if table exists
        if (!Schema::hasTable('attendance_settings')) {
            Schema::create('attendance_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->unique()->constrained()->onDelete('cascade');
                
                // Day-wise settings
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->integer('late_threshold_minutes')->default(15);
                $table->integer('half_day_threshold_hours')->default(4);
                
                // Period settings
                $table->boolean('is_period_based')->default(false);
                $table->json('period_schedule')->nullable();
                
                // Working days
                $table->json('working_days')->nullable();
                
                // Additional settings
                $table->boolean('auto_calculate_hours')->default(true);
                $table->boolean('enable_overtime')->default(false);
                $table->decimal('overtime_rate', 10, 2)->nullable();
                $table->boolean('enable_early_departure')->default(false);
                $table->integer('early_departure_minutes')->nullable();
                
                $table->timestamps();
            });
        } else {
            // Table exists, add missing columns
            Schema::table('attendance_settings', function (Blueprint $table) {
                // Define columns to add
                $columnsToAdd = [
                    'period_schedule' => 'json',
                    'working_days' => 'json',
                    'auto_calculate_hours' => 'boolean',
                    'enable_overtime' => 'boolean',
                    'overtime_rate' => 'decimal',
                    'enable_early_departure' => 'boolean',
                    'early_departure_minutes' => 'integer',
                ];
                
                foreach ($columnsToAdd as $columnName => $type) {
                    if (!Schema::hasColumn('attendance_settings', $columnName)) {
                        switch ($type) {
                            case 'json':
                                $table->json($columnName)->nullable();
                                break;
                            case 'boolean':
                                $table->boolean($columnName)->default(false);
                                break;
                            case 'decimal':
                                $table->decimal($columnName, 10, 2)->nullable();
                                break;
                            case 'integer':
                                $table->integer($columnName)->nullable();
                                break;
                        }
                    }
                }
            });
        }
    }

    public function down()
    {
        // We won't drop the table if it has data
        // Just remove newly added columns if needed
        Schema::table('attendance_settings', function (Blueprint $table) {
            $columnsToRemove = [
                'period_schedule',
                'working_days',
                'auto_calculate_hours',
                'enable_overtime',
                'overtime_rate',
                'enable_early_departure',
                'early_departure_minutes'
            ];
            
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('attendance_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};