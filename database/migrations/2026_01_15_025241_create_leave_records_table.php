<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leave_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            
            // Leave Types (Nepali context)
            $table->enum('leave_type', [
                'sick_leave', // बिरामी विदा
                'casual_leave', // साधारण विदा
                'annual_leave', // वार्षिक विदा
                'maternity_leave', // प्रसूति विदा
                'paternity_leave', // पितृत्व विदा
                'study_leave', // अध्ययन विदा
                'bereavement_leave', // शोक विदा
                'public_holiday', // सार्वजनिक बिदा
                'unpaid_leave' // बिना सवेतन विदा
            ]);
            
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            $table->text('reason');
            $table->string('contact_during_leave')->nullable();
            
            // Approval
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_remarks')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_records');
    }
};