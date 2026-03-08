<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Make all optional fields nullable
            $table->string('middle_name')->nullable()->change();
            $table->string('alternate_phone')->nullable()->change();
            $table->date('date_of_birth')->nullable()->change();
            $table->string('marital_status')->nullable()->change();
            $table->string('blood_group')->nullable()->change();
            $table->string('citizenship_number')->nullable()->change();
            $table->date('citizenship_issue_date')->nullable()->change();
            $table->string('citizenship_issued_district')->nullable()->change();
            $table->string('pan_number')->nullable()->change();
            $table->text('current_address')->nullable()->change();
            $table->text('permanent_address')->nullable()->change();
            $table->string('district')->nullable()->change();
            $table->string('municipality')->nullable()->change();
            $table->string('ward_number')->nullable()->change();
            $table->string('emergency_contact_name')->nullable()->change();
            $table->string('emergency_contact_phone')->nullable()->change();
            $table->string('emergency_contact_relation')->nullable()->change();
            $table->date('contract_end_date')->nullable()->change();
            $table->string('qualification')->nullable()->change();
            $table->string('institution_name')->nullable()->change();
            $table->integer('experience_years')->nullable()->change();
            $table->string('employment_status')->nullable()->change();
            $table->text('notes')->nullable()->change();
        });
    }

    public function down()
    {
        // Optional: define rollback
    }
};