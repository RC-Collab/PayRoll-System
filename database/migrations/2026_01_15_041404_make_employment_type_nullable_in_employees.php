<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Make employment_type nullable with default
            if (Schema::hasColumn('employees', 'employment_type')) {
                $table->string('employment_type')->nullable()->default('Full-time')->change();
            }
            
            // Also make other required columns nullable
            $nullableColumns = ['department', 'designation', 'nationality'];
            foreach ($nullableColumns as $column) {
                if (Schema::hasColumn('employees', $column)) {
                    $table->string($column)->nullable()->change();
                }
            }
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'employment_type')) {
                $table->string('employment_type')->nullable(false)->change();
            }
        });
    }
};