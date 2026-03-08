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
        // Qualifications add new optional columns
        if (Schema::hasTable('qualifications')) {
            Schema::table('qualifications', function (Blueprint $table) {
                if (!Schema::hasColumn('qualifications', 'board')) {
                    $table->string('board')->nullable();
                }
                if (!Schema::hasColumn('qualifications', 'start_date')) {
                    $table->date('start_date')->nullable();
                }
                if (!Schema::hasColumn('qualifications', 'end_date')) {
                    $table->date('end_date')->nullable();
                }
                if (!Schema::hasColumn('qualifications', 'is_pursuing')) {
                    $table->boolean('is_pursuing')->default(false);
                }
            });
        }

        // Experiences add location and achievements
        if (Schema::hasTable('experiences')) {
            Schema::table('experiences', function (Blueprint $table) {
                if (!Schema::hasColumn('experiences', 'location')) {
                    $table->string('location')->nullable();
                }
                if (!Schema::hasColumn('experiences', 'achievements')) {
                    $table->text('achievements')->nullable();
                }
            });
        }

        // Emergency contacts add phone2 and address
        if (Schema::hasTable('emergency_contacts')) {
            Schema::table('emergency_contacts', function (Blueprint $table) {
                if (!Schema::hasColumn('emergency_contacts', 'phone2')) {
                    $table->string('phone2')->nullable();
                }
                if (!Schema::hasColumn('emergency_contacts', 'address')) {
                    $table->string('address')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('qualifications')) {
            Schema::table('qualifications', function (Blueprint $table) {
                if (Schema::hasColumn('qualifications', 'board')) {
                    $table->dropColumn('board');
                }
                if (Schema::hasColumn('qualifications', 'start_date')) {
                    $table->dropColumn('start_date');
                }
                if (Schema::hasColumn('qualifications', 'end_date')) {
                    $table->dropColumn('end_date');
                }
                if (Schema::hasColumn('qualifications', 'is_pursuing')) {
                    $table->dropColumn('is_pursuing');
                }
            });
        }

        if (Schema::hasTable('experiences')) {
            Schema::table('experiences', function (Blueprint $table) {
                if (Schema::hasColumn('experiences', 'location')) {
                    $table->dropColumn('location');
                }
                if (Schema::hasColumn('experiences', 'achievements')) {
                    $table->dropColumn('achievements');
                }
            });
        }

        if (Schema::hasTable('emergency_contacts')) {
            Schema::table('emergency_contacts', function (Blueprint $table) {
                if (Schema::hasColumn('emergency_contacts', 'phone2')) {
                    $table->dropColumn('phone2');
                }
                if (Schema::hasColumn('emergency_contacts', 'address')) {
                    $table->dropColumn('address');
                }
            });
        }
    }
};
