<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('monthly_salaries')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('monthly_salaries', function (Blueprint $table) {
            // Fix salary_month to be a string/date format, not datetime
            $table->string('salary_month', 7)->change();
            
            // Make salary_year nullable
            $table->integer('salary_year')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('monthly_salaries')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('monthly_salaries', function (Blueprint $table) {
            $table->date('salary_month')->change();
            $table->integer('salary_year')->nullable(false)->change();
        });
    }
};