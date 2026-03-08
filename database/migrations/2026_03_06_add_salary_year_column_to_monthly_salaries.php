<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {        if (! Schema::hasTable('monthly_salaries')) {
            return;
        }
        Schema::table('monthly_salaries', function (Blueprint $table) {
            if (! Schema::hasColumn('monthly_salaries', 'salary_year')) {
                // year as integer, nullable for now; controller will populate it
                $table->smallInteger('salary_year')->nullable()->after('salary_month');
            }
        });

        // fill existing records if possible
        try {
            DB::table('monthly_salaries')
                ->whereNull('salary_year')
                ->update(['salary_year' => DB::raw('YEAR(salary_month)')]);
        } catch (\Exception $e) {
            // If the table doesn't exist or column can't be updated, ignore.
        }
    }

    public function down()
    {
        Schema::table('monthly_salaries', function (Blueprint $table) {
            if (Schema::hasColumn('monthly_salaries', 'salary_year')) {
                $table->dropColumn('salary_year');
            }
        });
    }
};
