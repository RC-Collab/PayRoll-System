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
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('key')->after('id')->unique();
            $table->text('value')->after('key')->nullable();
            $table->string('group')->after('value')->nullable();
            $table->string('type')->after('group')->default('text');
            $table->json('options')->after('type')->nullable();
            $table->text('description')->after('options')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn(['key', 'value', 'group', 'type', 'options', 'description']);
        });
    }
};
