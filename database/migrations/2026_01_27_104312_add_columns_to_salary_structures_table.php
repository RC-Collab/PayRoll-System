<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('salary_structures', function (Blueprint $table) {
            // Check if columns already exist
            if (!Schema::hasColumn('salary_structures', 'basic_salary')) {
                $table->decimal('basic_salary', 12, 2)->default(0)->after('employee_id');
            }
            
            if (!Schema::hasColumn('salary_structures', 'overtime_rate')) {
                $table->decimal('overtime_rate', 10, 2)->default(0)->comment('Per hour overtime rate')->after('basic_salary');
            }
            
            if (!Schema::hasColumn('salary_structures', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('overtime_rate');
            }
            
            if (!Schema::hasColumn('salary_structures', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
        });
    }

    public function down()
    {
        Schema::table('salary_structures', function (Blueprint $table) {
            // Only drop columns if they exist
            $columns = ['basic_salary', 'overtime_rate', 'created_by', 'updated_by'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('salary_structures', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};