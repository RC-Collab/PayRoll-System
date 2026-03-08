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
    Schema::table('department_employee', function (Blueprint $table) {
        $table->date('joined_date')->nullable()->after('role');
    });
}

public function down(): void
{
    Schema::table('department_employee', function (Blueprint $table) {
        $table->dropColumn('joined_date');
    });
}
};
