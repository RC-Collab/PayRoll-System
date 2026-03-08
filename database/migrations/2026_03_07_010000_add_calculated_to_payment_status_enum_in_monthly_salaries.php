<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // add 'calculated' status to the payment_status enum
        if (! Schema::hasTable('monthly_salaries')) {
            return;
        }

        // skip enum changes on sqlite (not supported)
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('monthly_salaries', function (Blueprint $table) {
            // note: changing an enum requires specifying the full set of allowed
            // values; keep the existing ones plus the new 'calculated'.
            $table->enum('payment_status', [
                'pending',
                'calculated',
                'paid',
                'hold',
                'cancelled',
            ])->default('pending')->change();
        });
    }

    public function down(): void
    {
        // revert to the original enum without 'calculated'
        if (! Schema::hasTable('monthly_salaries')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('monthly_salaries', function (Blueprint $table) {
            $table->enum('payment_status', [
                'pending',
                'paid',
                'hold',
                'cancelled',
            ])->default('pending')->change();
        });
    }
};