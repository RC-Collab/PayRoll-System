<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    if (!Schema::hasTable('salary_structures')) {
        return;
    }

    Schema::table('salary_structures', function ($table) {
        if (!Schema::hasColumn('salary_structures', 'deleted_at')) {
            $table->softDeletes(); // adds deleted_at column
        }
    });
}

public function down()
{
    Schema::table('salary_structures', function ($table) {
        $table->dropSoftDeletes();
    });
}
};
