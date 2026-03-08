<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained()->onDelete('cascade');
            
            // Basic Salary
            $table->decimal('basic_salary', 12, 2)->default(0);
            
            // Nepal Standard Allowances
            $table->decimal('dearness_allowance', 12, 2)->default(0); // महँगी भत्ता
            $table->decimal('house_rent_allowance', 12, 2)->default(0); // भाडा भत्ता
            $table->decimal('medical_allowance', 12, 2)->default(0); // चिकित्सा भत्ता
            
            // Optional Allowances
            $table->decimal('tiffin_allowance', 12, 2)->default(0); // खाजा भत्ता
            $table->decimal('transport_allowance', 12, 2)->default(0); // यातायात भत्ता
            $table->decimal('special_allowance', 12, 2)->default(0); // विशेष भत्ता
            
            // Overtime Rate
            $table->decimal('overtime_rate', 10, 2)->default(0); // Per hour rate
            
            // Deduction Settings
            $table->boolean('provident_fund_enabled')->default(true);
            $table->decimal('provident_fund_percentage', 5, 2)->default(10.00); // 10% default
            
            $table->decimal('citizen_investment', 12, 2)->default(0); // Fixed monthly amount
            
            // Tax Settings
            $table->boolean('tax_exempt')->default(false);
            $table->decimal('tax_deduction_source', 5, 2)->default(1.5); // TDS percentage
            
            // Additional Settings
            $table->decimal('daily_rate', 10, 2)->nullable(); // For penalty calculations
            $table->decimal('hourly_rate', 10, 2)->nullable(); // For overtime calculations
            
            $table->text('remarks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_structures');
    }
};