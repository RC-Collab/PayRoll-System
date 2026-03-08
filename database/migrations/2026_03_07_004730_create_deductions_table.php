<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deductions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Provident Fund", "CIT"
            $table->string('code')->unique(); // e.g., "PF", "CIT"
            $table->enum('type', ['fixed', 'percentage', 'formula'])->default('percentage');
            $table->decimal('default_value', 15, 2)->default(0);
            $table->decimal('percentage', 5, 2)->nullable(); // if percentage type
            $table->decimal('max_amount', 15, 2)->nullable(); // maximum limit
            $table->text('formula')->nullable(); // if formula type
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deductions');
    }
};