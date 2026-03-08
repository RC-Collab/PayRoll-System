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
            if (! Schema::hasColumn('monthly_salaries', 'paid_by')) {
                $table->foreignId('paid_by')->nullable()->constrained('users')->after('paid_at');
            }
        });
    }

    public function down()
    {
        Schema::table('monthly_salaries', function (Blueprint $table) {
            if (Schema::hasColumn('monthly_salaries', 'paid_by')) {
                $table->dropConstrainedForeignId('paid_by');
            }
        });
    }
};