<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Add missing columns that are in your form
            if (!Schema::hasColumn('employees', 'designation')) {
                $table->string('designation')->nullable()->after('employee_type');
            }
            
            if (!Schema::hasColumn('employees', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('experience_years');
            }
            
            if (!Schema::hasColumn('employees', 'account_number')) {
                $table->string('account_number')->nullable()->after('bank_name');
            }
            
            if (!Schema::hasColumn('employees', 'account_holder_name')) {
                $table->string('account_holder_name')->nullable()->after('account_number');
            }
            
            if (!Schema::hasColumn('employees', 'branch_name')) {
                $table->string('branch_name')->nullable()->after('account_holder_name');
            }
            
            if (!Schema::hasColumn('employees', 'middle_name')) {
                $table->string('middle_name')->nullable()->after('first_name');
            }
            
            if (!Schema::hasColumn('employees', 'alternate_phone')) {
                $table->string('alternate_phone')->nullable()->after('mobile_number');
            }
            
            if (!Schema::hasColumn('employees', 'qualification')) {
                $table->string('qualification')->nullable()->after('designation');
            }
            
            if (!Schema::hasColumn('employees', 'institution_name')) {
                $table->string('institution_name')->nullable()->after('qualification');
            }
            
            if (!Schema::hasColumn('employees', 'experience_years')) {
                $table->integer('experience_years')->nullable()->after('institution_name');
            }
            
            if (!Schema::hasColumn('employees', 'notes')) {
                $table->text('notes')->nullable()->after('branch_name');
            }
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            // You can optionally remove columns in down method
            $table->dropColumn([
                'designation', 'bank_name', 'account_number', 
                'account_holder_name', 'branch_name', 'middle_name',
                'alternate_phone', 'qualification', 'institution_name',
                'experience_years', 'notes'
            ]);
        });
    }
};