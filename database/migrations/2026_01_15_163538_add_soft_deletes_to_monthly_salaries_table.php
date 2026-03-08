<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // guard: if the table doesn't exist yet (fresh install/SQLite) skip alterations
        if (!Schema::hasTable('monthly_salaries')) {
            return;
        }

        Schema::table('monthly_salaries', function (Blueprint $table) {
            // Add deleted_at column if it doesn't exist
            if (!Schema::hasColumn('monthly_salaries', 'deleted_at')) {
                $table->softDeletes();
            }
            
            // Also make sure salary_month is a date field
            if (Schema::hasColumn('monthly_salaries', 'salary_month')) {
                // Check if it's integer type, change to date
                $columnType = Schema::getColumnType('monthly_salaries', 'salary_month');
                if ($columnType === 'integer') {
                    // First create a temporary column
                    $table->date('temp_salary_month')->nullable();
                    
                    // Update data from old format (e.g., 2024-01)
                    // We'll handle this in the controller instead
                }
            }
        });
    }

    public function down()
    {
        Schema::table('monthly_salaries', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};