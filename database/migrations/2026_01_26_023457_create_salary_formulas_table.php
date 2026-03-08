<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('salary_custom_components', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['allowance', 'deduction', 'bonus', 'other']);
            $table->enum('calculation_type', ['fixed', 'percentage', 'formula']);
            $table->decimal('fixed_amount', 10, 2)->nullable();
            $table->decimal('percentage_amount', 5, 2)->nullable();
            $table->string('formula')->nullable();
            $table->string('formula_variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('apply_to_all')->default(true);
            $table->json('applicable_departments')->nullable();
            $table->json('applicable_employees')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_custom_components');
    }
};