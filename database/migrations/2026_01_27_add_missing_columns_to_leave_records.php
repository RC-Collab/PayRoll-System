<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('leave_records', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('leave_records', 'medical_certificate')) {
                $table->boolean('medical_certificate')->default(false)->after('contact_during_leave');
            }
            
            if (!Schema::hasColumn('leave_records', 'rejected_by')) {
                $table->foreignId('rejected_by')->nullable()->constrained('users')->after('approved_at');
            }
            
            if (!Schema::hasColumn('leave_records', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
            
            if (!Schema::hasColumn('leave_records', 'remarks')) {
                $table->text('remarks')->nullable()->after('rejected_at');
            }
        });
    }

    public function down()
    {
        Schema::table('leave_records', function (Blueprint $table) {
            $table->dropColumnIfExists(['medical_certificate', 'rejected_by', 'rejected_at', 'remarks']);
        });
    }
};
