<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            
            // Personal Information
            $table->string('employee_code')->unique();
            $table->string('first_name');
            $table->string('middle_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('mobile_number');
            $table->string('alternate_phone');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->date('date_of_birth');
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed']);
            $table->enum('blood_group', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);
            
            // Citizenship & Identification
            $table->string('citizenship_number')->unique();
            $table->date('citizenship_issue_date');
            $table->string('citizenship_issued_district');
            $table->string('pan_number');
            
            // Address
            $table->text('current_address');
            $table->text('permanent_address');
            $table->string('district');
            $table->string('municipality');
            $table->string('ward_number');
            
            // Emergency Contact
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone');
            $table->string('emergency_contact_relation');
            
            // Employment Details
            $table->date('joining_date');
            $table->enum('employee_type', ['permanent', 'contract', 'temporary', 'probation', 'part-time']);
            $table->enum('employment_status', ['active', 'inactive', 'on-leave', 'suspended', 'terminated']);
            $table->date('contract_end_date');
            $table->string('designation');
            $table->string('qualification');
            $table->string('institution_name');
            $table->integer('experience_years');
            
            // Bank Details
            $table->string('bank_name');
            $table->string('account_number');
            $table->string('account_holder_name');
            $table->string('branch_name');
            
            // Other
            $table->string('profile_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
};