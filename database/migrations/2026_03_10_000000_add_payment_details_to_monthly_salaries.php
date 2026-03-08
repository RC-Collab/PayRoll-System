<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('monthly_salaries')) {
            return;
        }

        Schema::table('monthly_salaries', function (Blueprint $table) {
            if (! Schema::hasColumn('monthly_salaries', 'payment_bank')) {
                $table->string('payment_bank')->nullable()->after('payment_method');
            }

            if (! Schema::hasColumn('monthly_salaries', 'cheque_number')) {
                $table->string('cheque_number')->nullable()->after('payment_bank');
            }

            if (! Schema::hasColumn('monthly_salaries', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->nullable()->after('net_salary');
            }
        });
    }

    public function down()
    {
        Schema::table('monthly_salaries', function (Blueprint $table) {
            $table->dropColumn(['payment_bank', 'cheque_number', 'paid_amount']);
        });
    }
};