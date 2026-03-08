<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_settings', function (Blueprint $table) {
            $table->id();
            $table->string('fiscal_year')->default('2082/83');
            $table->boolean('is_active')->default(true);
            
            // Tax slabs for unmarried
            $table->json('unmarried_slabs')->nullable();
            
            // Tax slabs for married
            $table->json('married_slabs')->nullable();
            
            // Standard deductions limits
            $table->decimal('pf_percentage', 5, 2)->default(10.00);
            $table->decimal('ssf_employee_percentage', 5, 2)->default(11.00);
            $table->decimal('ssf_employer_percentage', 5, 2)->default(20.00);
            $table->decimal('cit_max_amount', 15, 2)->default(300000);
            $table->decimal('insurance_max_amount', 15, 2)->default(40000);
            $table->decimal('medical_max_amount', 15, 2)->default(20000);
            $table->decimal('remote_area_max_amount', 15, 2)->default(50000);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_settings');
    }
};