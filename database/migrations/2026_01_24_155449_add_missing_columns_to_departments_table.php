<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('departments')) {
            return;
        }

        Schema::table('departments', function (Blueprint $table) {
            // Check if columns exist before adding them
            if (!Schema::hasColumn('departments', 'category')) {
                $table->string('category')->default('administrative')->after('description');
            }
            
            if (!Schema::hasColumn('departments', 'head_of_department')) {
                $table->string('head_of_department')->nullable()->after('category');
            }
            
            if (!Schema::hasColumn('departments', 'icon')) {
                $table->string('icon')->nullable()->after('head_of_department');
            }
            
            if (!Schema::hasColumn('departments', 'roles')) {
                $table->json('roles')->nullable()->after('icon');
            }
            
            if (!Schema::hasColumn('departments', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('roles');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('departments')) {
            return;
        }

        Schema::table('departments', function (Blueprint $table) {
            // Remove columns if they exist
            $columns = ['category', 'head_of_department', 'icon', 'roles', 'is_active'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('departments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};