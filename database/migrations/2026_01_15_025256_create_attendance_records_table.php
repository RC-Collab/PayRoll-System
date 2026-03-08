<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('attendance_date');
            
            // Check-in/Check-out
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->decimal('working_hours', 5, 2)->default(0);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            
            // Status
            $table->enum('status', [
                'present',
                'absent',
                'half_day',
                'leave',
                'holiday',
                'weekend'
            ])->default('absent');
            
            $table->string('remarks')->nullable();
            $table->timestamps();
            
            // Unique constraint for employee per day
            $table->unique(['employee_id', 'attendance_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_records');
    }
};