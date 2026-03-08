<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('holidays');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('name');
            $table->enum('type', ['national', 'organization', 'optional'])->default('organization');
            $table->unsignedBigInteger('organization_id')->default(1);
            $table->timestamps();
        });
    }
};