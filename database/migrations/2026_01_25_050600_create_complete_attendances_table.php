<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // If attendance table already exists the earlier migration(s) have taken care of it,
        // this migration is intended as a "complete" replacement. When running tests the
        // fresh database will execute the original create migration first, so we can safely
        // skip doing anything here when the table already exists.
        if (Schema::hasTable('attendances')) {
            return;
        }

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'leave', 'half_day', 'holiday', 'weekend'])->default('absent');
            $table->boolean('is_late')->default(false);
            $table->integer('late_minutes')->nullable();
            $table->integer('early_departure_minutes')->nullable();
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->decimal('total_hours', 5, 2)->nullable();
            $table->integer('overtime_minutes')->nullable();
            $table->enum('attendance_type', ['day_wise', 'period_wise'])->default('day_wise');
            $table->string('shift_type')->nullable();
            $table->text('remarks')->nullable();
            $table->text('notes')->nullable();
            
            // Period attendance columns
            $table->enum('p1', ['P', 'A', 'L', 'H', '-'])->default('-');
            $table->enum('p2', ['P', 'A', 'L', 'H', '-'])->default('-');
            $table->enum('p3', ['P', 'A', 'L', 'H', '-'])->default('-');
            $table->enum('p4', ['P', 'A', 'L', 'H', '-'])->default('-');
            $table->enum('p5', ['P', 'A', 'L', 'H', '-'])->default('-');
            $table->enum('p6', ['P', 'A', 'L', 'H', '-'])->default('-');
            $table->enum('p7', ['P', 'A', 'L', 'H', '-'])->default('-');
            $table->enum('p8', ['P', 'A', 'L', 'H', '-'])->default('-');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['employee_id', 'date']);
            $table->index(['date', 'status']);
            $table->unique(['employee_id', 'date']);
        });
    }

    public function down()
    {
        // Don't drop table if it has data
        // Only remove newly added columns if needed
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                $columnsToRemove = [
                    'late_minutes',
                    'early_departure_minutes', 
                    'total_hours',
                    'overtime_minutes',
                    'attendance_type',
                    'shift_type',
                    'remarks',
                    'p1', 'p2', 'p3', 'p4', 'p5', 'p6', 'p7', 'p8'
                ];
                
                foreach ($columnsToRemove as $column) {
                    if (Schema::hasColumn('attendances', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};