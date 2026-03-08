<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activation_codes', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('phone');
            $table->string('code', 6);
            $table->boolean('is_used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();
            
            $table->index(['email', 'phone']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('activation_codes');
    }
};