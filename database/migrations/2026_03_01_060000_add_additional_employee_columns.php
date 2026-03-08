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
        Schema::table('employees', function (Blueprint $table) {
            // Add religion column if it doesn't exist
            if (!Schema::hasColumn('employees', 'religion')) {
                $table->string('religion')->nullable();
            }

            // Add reports_to column for manager relationship
            if (!Schema::hasColumn('employees', 'reports_to')) {
                $table->unsignedBigInteger('reports_to')->nullable();
                $table->foreign('reports_to')->references('id')->on('employees')->onDelete('set null');
            }

            // Add probation_end_date
            if (!Schema::hasColumn('employees', 'probation_end_date')) {
                $table->date('probation_end_date')->nullable();
            }

            // Add work_shift
            if (!Schema::hasColumn('employees', 'work_shift')) {
                $table->string('work_shift')->nullable();
            }

            // Add salary-related columns
            if (!Schema::hasColumn('employees', 'uan_number')) {
                $table->string('uan_number')->nullable();
            }

            if (!Schema::hasColumn('employees', 'esi_number')) {
                $table->string('esi_number')->nullable();
            }

            // Add ifsc_code if not already added
            if (!Schema::hasColumn('employees', 'ifsc_code')) {
                $table->string('ifsc_code')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $columns = ['religion', 'reports_to', 'probation_end_date', 'work_shift', 'uan_number', 'esi_number', 'ifsc_code'];
            foreach ($columns as $column) {
                if ($column === 'reports_to') {
                    if (Schema::hasColumn('employees', 'reports_to')) {
                        $table->dropForeign(['reports_to']);
                        $table->dropColumn('reports_to');
                    }
                } else {
                    if (Schema::hasColumn('employees', $column)) {
                        $table->dropColumn($column);
                    }
                }
            }
        });
    }
};
