<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('period_attendance')) {
            Schema::create('period_attendance', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained()->onDelete('cascade');
                $table->date('date');
                $table->enum('p1', ['P', 'A', 'L', 'H', '-'])->default('-');
                $table->enum('p2', ['P', 'A', 'L', 'H', '-'])->default('-');
                $table->enum('p3', ['P', 'A', 'L', 'H', '-'])->default('-');
                $table->enum('p4', ['P', 'A', 'L', 'H', '-'])->default('-');
                $table->enum('p5', ['P', 'A', 'L', 'H', '-'])->default('-');
                $table->enum('p6', ['P', 'A', 'L', 'H', '-'])->default('-');
                $table->enum('p7', ['P', 'A', 'L', 'H', '-'])->default('-');
                $table->enum('p8', ['P', 'A', 'L', 'H', '-'])->default('-');
                $table->json('extra_periods')->nullable();
                $table->integer('total_present')->default(0);
                $table->integer('total_absent')->default(0);
                $table->text('notes')->nullable();
                
                $table->unique(['employee_id', 'date']);
                $table->timestamps();
                
                $table->index(['employee_id', 'date']);
            });
            
            echo "Created period_attendance table.\n";
        } else {
            echo "period_attendance table already exists.\n";
            
            // Check and add missing columns
            Schema::table('period_attendance', function (Blueprint $table) {
                $columns = [
                    'extra_periods' => function() use ($table) {
                        if (!Schema::hasColumn('period_attendance', 'extra_periods')) {
                            $table->json('extra_periods')->nullable();
                            echo "Added extra_periods column.\n";
                        }
                    },
                    'total_present' => function() use ($table) {
                        if (!Schema::hasColumn('period_attendance', 'total_present')) {
                            $table->integer('total_present')->default(0);
                            echo "Added total_present column.\n";
                        }
                    },
                    'total_absent' => function() use ($table) {
                        if (!Schema::hasColumn('period_attendance', 'total_absent')) {
                            $table->integer('total_absent')->default(0);
                            echo "Added total_absent column.\n";
                        }
                    },
                ];
                
                foreach ($columns as $columnCheck) {
                    $columnCheck();
                }
            });
        }
    }

    public function down()
    {
        Schema::table('period_attendance', function (Blueprint $table) {
            $columns = [
                'extra_periods',
                'total_present',
                'total_absent'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('period_attendance', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};