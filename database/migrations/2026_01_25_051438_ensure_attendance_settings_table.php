<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if table exists - if not, create it
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
            
            echo "Created attendance_settings table.\n";
        } else {
            echo "attendance_settings table already exists.\n";
            
            // Check and add missing columns
            Schema::table('attendance_settings', function (Blueprint $table) {
                // List of columns that should exist
                $columns = [
                    'period_schedule' => function() use ($table) {
                        if (!Schema::hasColumn('attendance_settings', 'period_schedule')) {
                            $table->json('period_schedule')->nullable();
                            echo "Added period_schedule column.\n";
                        }
                    },
                    'working_days' => function() use ($table) {
                        if (!Schema::hasColumn('attendance_settings', 'working_days')) {
                            $table->json('working_days')->nullable();
                            echo "Added working_days column.\n";
                        }
                    },
                    'auto_calculate_hours' => function() use ($table) {
                        if (!Schema::hasColumn('attendance_settings', 'auto_calculate_hours')) {
                            $table->boolean('auto_calculate_hours')->default(true);
                            echo "Added auto_calculate_hours column.\n";
                        }
                    },
                    'enable_overtime' => function() use ($table) {
                        if (!Schema::hasColumn('attendance_settings', 'enable_overtime')) {
                            $table->boolean('enable_overtime')->default(false);
                            echo "Added enable_overtime column.\n";
                        }
                    },
                    'overtime_rate' => function() use ($table) {
                        if (!Schema::hasColumn('attendance_settings', 'overtime_rate')) {
                            $table->decimal('overtime_rate', 10, 2)->nullable();
                            echo "Added overtime_rate column.\n";
                        }
                    },
                    'enable_early_departure' => function() use ($table) {
                        if (!Schema::hasColumn('attendance_settings', 'enable_early_departure')) {
                            $table->boolean('enable_early_departure')->default(false);
                            echo "Added enable_early_departure column.\n";
                        }
                    },
                    'early_departure_minutes' => function() use ($table) {
                        if (!Schema::hasColumn('attendance_settings', 'early_departure_minutes')) {
                            $table->integer('early_departure_minutes')->nullable();
                            echo "Added early_departure_minutes column.\n";
                        }
                    },
                ];
                
                // Execute each check
                foreach ($columns as $columnCheck) {
                    $columnCheck();
                }
            });
        }
    }

    public function down()
    {
        // We won't drop the table to preserve data
        // Just remove the newly added columns if they exist
        Schema::table('attendance_settings', function (Blueprint $table) {
            $columns = [
                'period_schedule',
                'working_days',
                'auto_calculate_hours',
                'enable_overtime',
                'overtime_rate',
                'enable_early_departure',
                'early_departure_minutes'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('attendance_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};