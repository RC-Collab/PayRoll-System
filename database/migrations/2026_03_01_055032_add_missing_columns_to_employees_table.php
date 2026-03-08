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
            // Add alternative_number if it doesn't exist
            if (!Schema::hasColumn('employees', 'alternative_number')) {
                $table->string('alternative_number')->nullable();
            }
            
            // Add new address fields if they don't exist
            if (!Schema::hasColumn('employees', 'present_address')) {
                $table->text('present_address')->nullable();
            }
            
            if (!Schema::hasColumn('employees', 'city')) {
                $table->string('city')->nullable();
            }
            
            if (!Schema::hasColumn('employees', 'state')) {
                $table->string('state')->nullable();
            }
            
            if (!Schema::hasColumn('employees', 'country')) {
                $table->string('country')->nullable();
            }
            
            if (!Schema::hasColumn('employees', 'postal_code')) {
                $table->string('postal_code')->nullable();
            }
            
            // Add ifsc_code if it doesn't exist
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
            $columns = ['alternative_number', 'present_address', 'city', 'state', 'country', 'postal_code', 'ifsc_code'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('employees', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
