<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('salary_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->boolean('enable_insurance')->default(false);
            $table->decimal('insurance_percentage', 5, 2)->default(0);
            $table->decimal('insurance_fixed_amount', 10, 2)->nullable();
            $table->boolean('enable_late_deduction')->default(false);
            $table->decimal('late_deduction_per_hour', 10, 2)->default(0);
            $table->boolean('enable_absent_deduction')->default(false);
            $table->decimal('absent_deduction_per_day', 10, 2)->default(0);
            $table->boolean('enable_leave_deduction')->default(false);
            $table->decimal('leave_deduction_per_day', 10, 2)->default(0);
            $table->boolean('enable_overtime')->default(false);
            $table->decimal('overtime_rate_per_hour', 10, 2)->default(0);
            $table->decimal('working_days_per_month', 5, 2)->default(26);
            $table->decimal('working_hours_per_day', 5, 2)->default(8);
            $table->boolean('enable_provident_fund')->default(false);
            $table->decimal('provident_fund_percentage', 5, 2)->default(10);
            $table->boolean('enable_income_tax')->default(true);
            $table->boolean('enable_bonus')->default(false);
            $table->string('bonus_type')->nullable()->comment('fixed, percentage');
            $table->decimal('bonus_amount', 10, 2)->nullable();
            $table->json('custom_fields')->nullable();
            $table->json('formula_settings')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_settings');
    }
};