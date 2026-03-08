<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('salary_slips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_id')->constrained('monthly_salaries')->onDelete('cascade');
            $table->string('slip_number')->unique();
            $table->json('components_breakdown');
            $table->json('attendance_summary');
            $table->json('tax_calculation');
            $table->text('notes')->nullable();
            $table->string('issued_by');
            $table->date('issue_date');
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_slips');
    }
};