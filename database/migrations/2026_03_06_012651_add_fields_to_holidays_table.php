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
        Schema::table('holidays', function (Blueprint $table) {
            if (!Schema::hasColumn('holidays', 'description')) {
                $table->text('description')->nullable()->after('type');
            }
            if (!Schema::hasColumn('holidays', 'is_recurring')) {
                $table->boolean('is_recurring')->default(false)->after('description');
            }
            if (!Schema::hasColumn('holidays', 'year')) {
                $table->integer('year')->nullable()->after('is_recurring');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            if (Schema::hasColumn('holidays', 'year')) {
                $table->dropColumn('year');
            }
            if (Schema::hasColumn('holidays', 'is_recurring')) {
                $table->dropColumn('is_recurring');
            }
            if (Schema::hasColumn('holidays', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
