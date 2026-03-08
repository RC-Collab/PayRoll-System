<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['allowance', 'deduction', 'bonus', 'other']);
            $table->enum('calculation_type', ['fixed', 'percentage', 'formula', 'attendance_based']);
            $table->decimal('fixed_amount', 12, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->string('formula')->nullable();
            $table->string('attendance_field')->nullable()->comment('late_minutes, absent_days, etc');
            $table->decimal('attendance_rate', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('applicable_to', ['all', 'department', 'employee', 'designation']);
            $table->json('applicable_ids')->nullable();
            $table->integer('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_components');
    }
};