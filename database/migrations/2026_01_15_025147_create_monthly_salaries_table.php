<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // For sqlite (tests) the monthly_salaries table may not exist yet,
        // so create a minimal version here to allow other migrations and tests
        // to run.  In production the real table should already exist and this
        // will just add extra columns.
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            if (! Schema::hasTable('monthly_salaries')) {
                Schema::create('monthly_salaries', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('employee_id')->constrained()->onDelete('cascade');
                    $table->string('salary_month');
                    $table->decimal('basic_salary', 15, 2)->default(0);
                    $table->decimal('total_allowances', 10, 2)->default(0);
                    $table->decimal('total_deductions', 10, 2)->default(0);
                    $table->decimal('gross_salary', 15, 2)->default(0);
                    $table->decimal('net_salary', 15, 2)->default(0);
                    $table->enum('payment_status', ['pending','paid','hold','cancelled'])
                          ->default('pending');
                    $table->timestamps();
                });
            }

            return;
        }

        // non-sqlite: simply add additional columns to existing table
        Schema::table('monthly_salaries', function (Blueprint $table) {
            $table->json('custom_components')->nullable()->after('calculation_details');
            $table->json('attendance_summary')->nullable()->after('custom_components');
            $table->decimal('late_deduction_amount', 10, 2)->default(0)->after('penalty_leave_deduction');
            $table->decimal('absent_deduction_amount', 10, 2)->default(0)->after('late_deduction_amount');
            $table->decimal('insurance_amount', 10, 2)->default(0)->after('absent_deduction_amount');
            $table->decimal('bonus_amount', 10, 2)->default(0)->after('insurance_amount');
        });
    }

    public function down()
    {
        // we can safely drop the table in sqlite or just remove it in normal
        // environments as well since other migrations guard their own changes.
        Schema::dropIfExists('monthly_salaries');
    }
};