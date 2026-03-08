<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Skip if employees table doesn't exist yet
        if (!Schema::hasTable('employees')) {
            return;
        }

        Schema::table('employees', function (Blueprint $table) {
            // Add only columns that don't exist
            
            // Check and add middle_name if not exists
            if (!Schema::hasColumn('employees', 'middle_name')) {
                $table->string('middle_name')->nullable()->after('first_name');
            }
            
            // Rename phone to mobile_number if needed, or keep both
            if (!Schema::hasColumn('employees', 'mobile_number')) {
                $table->string('mobile_number')->nullable()->after('phone');
            }
            
            if (!Schema::hasColumn('employees', 'alternate_phone')) {
                $table->string('alternate_phone')->nullable()->after('mobile_number');
            }
            
            if (!Schema::hasColumn('employees', 'blood_group')) {
                $table->enum('blood_group', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable()->after('marital_status');
            }
            
            // Add citizenship details
            if (!Schema::hasColumn('employees', 'citizenship_number')) {
                $table->string('citizenship_number')->nullable()->unique()->after('national_id');
            }
            
            if (!Schema::hasColumn('employees', 'citizenship_issue_date')) {
                $table->date('citizenship_issue_date')->nullable()->after('citizenship_number');
            }
            
            if (!Schema::hasColumn('employees', 'citizenship_issued_district')) {
                $table->string('citizenship_issued_district')->nullable()->after('citizenship_issue_date');
            }
            
            if (!Schema::hasColumn('employees', 'pan_number')) {
                $table->string('pan_number')->nullable()->unique()->after('citizenship_issued_district');
            }
            
            // Split address into current and permanent
            if (!Schema::hasColumn('employees', 'current_address')) {
                $table->text('current_address')->nullable()->after('address');
            }
            
            if (!Schema::hasColumn('employees', 'permanent_address')) {
                $table->text('permanent_address')->nullable()->after('current_address');
            }
            
            if (!Schema::hasColumn('employees', 'district')) {
                $table->string('district')->nullable()->after('permanent_address');
            }
            
            if (!Schema::hasColumn('employees', 'municipality')) {
                $table->string('municipality')->nullable()->after('district');
            }
            
            if (!Schema::hasColumn('employees', 'ward_number')) {
                $table->string('ward_number')->nullable()->after('municipality');
            }
            
            // Emergency contact
            if (!Schema::hasColumn('employees', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('ward_number');
            }
            
            if (!Schema::hasColumn('employees', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            }
            
            if (!Schema::hasColumn('employees', 'emergency_contact_relation')) {
                $table->string('emergency_contact_relation')->nullable()->after('emergency_contact_phone');
            }
            
            // Employment details (you might already have some)
            if (!Schema::hasColumn('employees', 'joining_date')) {
                $table->date('joining_date')->nullable()->after('emergency_contact_relation');
            }
            
            if (!Schema::hasColumn('employees', 'employee_type')) {
                $table->enum('employee_type', ['permanent', 'contract', 'temporary', 'probation', 'part-time'])->default('permanent')->after('joining_date');
            }
            
            if (!Schema::hasColumn('employees', 'employment_status')) {
                $table->enum('employment_status', ['active', 'inactive', 'on-leave', 'suspended', 'terminated'])->default('active')->after('employee_type');
            }
            
            if (!Schema::hasColumn('employees', 'contract_end_date')) {
                $table->date('contract_end_date')->nullable()->after('employment_status');
            }
            
            if (!Schema::hasColumn('employees', 'position_title')) {
                $table->string('position_title')->nullable()->after('contract_end_date');
            }
            
            // Rename qualification field if needed
            if (!Schema::hasColumn('employees', 'qualification')) {
                $table->string('qualification')->nullable()->after('position_title');
            }
            
            if (!Schema::hasColumn('employees', 'institution_name')) {
                $table->string('institution_name')->nullable()->after('qualification');
            }
            
            if (!Schema::hasColumn('employees', 'experience_years')) {
                $table->integer('experience_years')->default(0)->after('institution_name');
            }
            
            // Bank details
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
            
            if (!Schema::hasColumn('employees', 'branch_address')) {
                $table->string('branch_address')->nullable()->after('branch_name');
            }
            
            // Documents
            if (!Schema::hasColumn('employees', 'profile_image')) {
                $table->string('profile_image')->nullable()->after('branch_address');
            }
            
            if (!Schema::hasColumn('employees', 'citizenship_front')) {
                $table->string('citizenship_front')->nullable()->after('profile_image');
            }
            
            if (!Schema::hasColumn('employees', 'citizenship_back')) {
                $table->string('citizenship_back')->nullable()->after('citizenship_front');
            }
            
            if (!Schema::hasColumn('employees', 'pan_certificate')) {
                $table->string('pan_certificate')->nullable()->after('citizenship_back');
            }
            
            if (!Schema::hasColumn('employees', 'resume')) {
                $table->string('resume')->nullable()->after('pan_certificate');
            }
            
            if (!Schema::hasColumn('employees', 'other_documents')) {
                $table->json('other_documents')->nullable()->after('resume');
            }
            
            if (!Schema::hasColumn('employees', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('other_documents');
            }
            
            if (!Schema::hasColumn('employees', 'notes')) {
                $table->text('notes')->nullable()->after('is_active');
            }
            
            // Soft deletes if not already there
            if (!Schema::hasColumn('employees', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down()
    {
        // Safe down migration - don't drop columns to avoid data loss
        Schema::table('employees', function (Blueprint $table) {
            // We're not dropping columns to preserve data
            // If you need to rollback, create a separate migration
        });
    }
};