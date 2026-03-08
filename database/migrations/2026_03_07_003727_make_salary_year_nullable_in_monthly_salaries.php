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
        if (! Schema::hasTable('monthly_salaries')) {
            return;
        }

        // sqlite cannot perform column changes easily; skip
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('monthly_salaries', function (Blueprint $table) {
            // Make salary_year nullable
            $table->integer('salary_year')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('monthly_salaries')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('monthly_salaries', function (Blueprint $table) {
            $table->integer('salary_year')->nullable(false)->change();
        });
    }
};