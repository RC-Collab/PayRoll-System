<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Only modify enum structure on MySQL; sqlite doesn't support ALTER ... MODIFY
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Update existing records from underscore format to simple format
        DB::table('leave_records')->update([
            'leave_type' => DB::raw("REPLACE(leave_type, '_', ' ')")
        ]);

        // Drop and recreate the enum column with correct values
        Schema::table('leave_records', function (Blueprint $table) {
            // This is database-specific, adjust for your database system
            // For MySQL:
            DB::statement("ALTER TABLE leave_records MODIFY leave_type ENUM('sick', 'casual', 'annual', 'maternity', 'paternity', 'bereavement', 'unpaid')");
        });
    }

    public function down()
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('leave_records', function (Blueprint $table) {
            DB::statement("ALTER TABLE leave_records MODIFY leave_type ENUM('sick_leave', 'casual_leave', 'annual_leave', 'maternity_leave', 'paternity_leave', 'study_leave', 'bereavement_leave', 'public_holiday', 'unpaid_leave')");
        });
    }
};
