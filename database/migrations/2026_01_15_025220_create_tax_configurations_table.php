<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tax_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('fiscal_year'); // आर्थिक वर्ष (e.g., 2080/81)
            $table->decimal('individual_tax_free_limit', 12, 2)->default(500000); // व्यक्तिगत करमुक्त सीमा (₹5,00,000)
            $table->json('tax_slabs'); // JSON of tax slabs
        
            // Example structure for tax_slabs JSON:
            // [
            //     {"from": 0, "to": 500000, "rate": 0, "fixed_amount": 0},
            //     {"from": 500000, "to": 700000, "rate": 10, "fixed_amount": 0},
            //     {"from": 700000, "to": 1000000, "rate": 20, "fixed_amount": 20000},
            //     {"from": 1000000, "to": 2000000, "rate": 30, "fixed_amount": 90000},
            //     {"from": 2000000, "to": null, "rate": 36, "fixed_amount": 390000}
            // ]
            
            $table->decimal('provident_fund_percentage', 5, 2)->default(10);
            $table->decimal('citizen_investment_percentage', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tax_configurations');
    }
};