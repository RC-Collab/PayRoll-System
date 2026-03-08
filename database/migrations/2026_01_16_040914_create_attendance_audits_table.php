<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceAuditsTable extends Migration
{
    public function up()
    {
        Schema::create('attendance_audits', function (Blueprint $table) {
            $table->id();
            $table->string('attendance_type'); // 'day_wise' or 'period_wise'
            $table->unsignedBigInteger('attendance_id'); // ID from respective table
            $table->json('old_data');
            $table->json('new_data')->nullable();
            $table->foreignId('changed_by')->constrained('users');
            $table->text('change_reason')->nullable();
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['attendance_type', 'attendance_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_audits');
    }
}