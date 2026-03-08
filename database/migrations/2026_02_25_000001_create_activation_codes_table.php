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
        if (!Schema::hasTable('activation_codes')) {
            Schema::create('activation_codes', function (Blueprint $table) {
                $table->id();
                $table->string('email');
                $table->string('phone');
                $table->string('code', 6);
                $table->dateTime('expires_at');
                $table->boolean('is_used')->default(false);
                $table->dateTime('used_at')->nullable();
                $table->timestamps();

                // Indexes for faster lookups
                $table->index(['email', 'phone']);
                $table->index(['code']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activation_codes');
    }
};
