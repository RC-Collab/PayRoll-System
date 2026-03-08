<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('changed_by_user_id')->nullable(); // Who changed it (admin/hr), null if employee changed themselves
            $table->string('action'); // 'created', 'updated', 'reset'
            $table->text('reason')->nullable(); // Why the password was changed
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('changed_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->index('user_id');
            $table->index('changed_by_user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_histories');
    }
};
