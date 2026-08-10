<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_posting_id')->constrained()->onDelete('cascade');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', [
                'submitted',
                'under_review',
                'screening',
                'shortlisted',
                'for_interview',
                'interviewed',
                'assessed',
                'recommended',
                'offer_sent',
                'offer_accepted',
                'offer_declined',
                'hired',
                'onboarding',
                'rejected',
                'withdrawn',
            ])->default('submitted');
            $table->string('cover_letter')->nullable();
            $table->dateTime('applied_at')->nullable();
            $table->dateTime('screening_date')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('screening_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->unique(['applicant_id', 'job_posting_id']);
            $table->index('status');
            $table->index('applied_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
