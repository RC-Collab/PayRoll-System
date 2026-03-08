<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Make optional fields nullable
            $table->string('middle_name')->nullable()->change();
            $table->string('alternate_phone')->nullable()->change();
            $table->date('contract_end_date')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('middle_name')->nullable(false)->change();
            $table->string('alternate_phone')->nullable(false)->change();
            $table->date('contract_end_date')->nullable(false)->change();
        });
    }
};