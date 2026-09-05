<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $create = static function (string $name, Closure $definition): void {
            if (!Schema::hasTable($name)) {
                Schema::create($name, $definition);
            }
        };

        // Framework tables.
        $create('users', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->rememberToken();
            $table->timestamps();
        });

        $create('password_reset_tokens', static function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        $create('sessions', static function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        $create('cache', static function (Blueprint $table): void {
            $table->string('key')->primary();
            $table->text('value');
            $table->integer('expiration')->index();
        });

        $create('cache_locks', static function (Blueprint $table): void {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration')->index();
        });

        $create('jobs', static function (Blueprint $table): void {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        $create('job_batches', static function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        $create('failed_jobs', static function (Blueprint $table): void {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        $create('personal_access_tokens', static function (Blueprint $table): void {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });

        // Roles and permissions.
        $create('permissions', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        $create('roles', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        $create('model_has_permissions', static function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        $create('model_has_roles', static function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        $create('role_has_permissions', static function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });

        // Recruitment source data.
        $create('departments', static function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        $create('job_positions', static function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('code')->nullable();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->text('responsibilities')->nullable();
            $table->text('requirements')->nullable();
            $table->text('qualifications')->nullable();
            $table->json('required_skills')->nullable();
            $table->json('preferred_skills')->nullable();
            $table->decimal('min_salary', 12, 2)->nullable();
            $table->decimal('max_salary', 12, 2)->nullable();
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'internship'])->default('full_time');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        $create('job_postings', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_position_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->integer('vacancies_count')->default(1);
            $table->json('required_skills')->nullable();
            $table->json('preferred_skills')->nullable();
            $table->text('requirements')->nullable();
            $table->text('qualifications')->nullable();
            $table->json('screening_questions')->nullable();
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'internship'])->default('full_time');
            $table->string('location')->nullable();
            $table->string('salary_range')->nullable();
            $table->string('source')->default('website');
            $table->date('posted_date')->nullable();
            $table->date('closing_date')->nullable();
            $table->decimal('estimated_cost', 12, 2)->nullable();
            $table->enum('status', ['draft', 'published', 'closed', 'filled'])->default('draft');
            $table->timestamps();
        });

        $create('applicants', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('nationality')->default('Philippines');
            $table->string('resume_path')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->text('summary')->nullable();
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->string('source')->default('website');
            $table->timestamps();
        });

        $create('applications', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference_code', 30)->nullable()->unique();
            $table->enum('status', ['submitted', 'under_review', 'screening', 'shortlisted', 'for_interview', 'interviewed', 'assessed', 'recommended', 'offer_sent', 'offer_accepted', 'offer_declined', 'hired', 'onboarding', 'rejected', 'withdrawn'])->default('submitted');
            $table->string('cover_letter')->nullable();
            $table->string('custom_resume_path')->nullable();
            $table->text('custom_notes')->nullable();
            $table->json('screening_answers')->nullable();
            $table->boolean('is_knocked_out')->default(false);
            $table->string('knockout_reason')->nullable();
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

        $create('applicant_education', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('institution');
            $table->string('degree')->nullable();
            $table->string('field_of_study')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('gpa', 3, 2)->nullable();
            $table->string('honors')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $create('applicant_experience', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('company');
            $table->string('job_title');
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->json('skills_used')->nullable();
            $table->timestamps();
        });

        $create('applicant_skills', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('skill');
            $table->enum('proficiency', ['beginner', 'intermediate', 'advanced', 'expert'])->default('intermediate');
            $table->integer('years_of_experience')->default(0);
            $table->timestamps();
            $table->index(['applicant_id', 'skill']);
        });

        $create('certifications', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('issuing_organization')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('credential_id')->nullable();
            $table->string('credential_url')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Hiring workflow.
        $create('interviews', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('interviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('scheduled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('scheduled_at');
            $table->string('location')->nullable();
            $table->string('meeting_link')->nullable();
            $table->enum('type', ['phone', 'video', 'in_person', 'technical', 'panel'])->default('in_person');
            $table->integer('round')->default(1);
            $table->integer('duration_minutes')->default(60);
            $table->text('notes')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'rescheduled', 'no_show'])->default('scheduled');
            $table->dateTime('reminder_sent_at')->nullable();
            $table->timestamps();
            $table->index(['scheduled_at', 'status']);
        });

        $create('interview_assessments', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('interview_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('communication_score')->default(0);
            $table->integer('technical_score')->default(0);
            $table->integer('experience_score')->default(0);
            $table->integer('cultural_fit_score')->default(0);
            $table->decimal('overall_score', 5, 2)->default(0);
            $table->text('strengths')->nullable();
            $table->text('weaknesses')->nullable();
            $table->text('comments')->nullable();
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->timestamps();
        });

        $create('ai_recommendations', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->integer('match_score')->default(0);
            $table->integer('skills_match_percentage')->default(0);
            $table->decimal('confidence_score', 5, 2)->default(0);
            $table->enum('recommendation', ['highly_recommended', 'recommended', 'moderately_recommended', 'not_recommended'])->default('moderately_recommended');
            $table->json('missing_skills')->nullable();
            $table->json('strengths')->nullable();
            $table->json('weaknesses')->nullable();
            $table->json('qualification_gaps')->nullable();
            $table->text('explanation')->nullable();
            $table->text('summary')->nullable();
            $table->json('score_breakdown')->nullable();
            $table->integer('rank')->nullable();
            $table->enum('status', ['generated', 'reviewed', 'approved', 'overridden'])->default('generated');
            $table->timestamps();
            $table->index(['job_posting_id', 'match_score']);
        });

        $create('offer_letters', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('offer_number')->unique();
            $table->decimal('salary', 12, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->string('employment_type')->default('full_time');
            $table->text('terms')->nullable();
            $table->text('benefits')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('response_at')->nullable();
            $table->enum('status', ['draft', 'sent', 'accepted', 'declined', 'expired'])->default('draft');
            $table->text('response_notes')->nullable();
            $table->timestamps();
        });

        // Onboarding and employee records.
        $create('onboarding_checklists', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('category', ['document', 'orientation', 'training', 'system', 'compliance'])->default('document');
            $table->boolean('is_required')->default(true);
            $table->integer('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        $create('onboarding', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('orientation_date')->nullable();
            $table->date('training_start')->nullable();
            $table->date('training_end')->nullable();
            $table->integer('progress')->default(0);
            $table->json('completed_checklist_ids')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'on_hold'])->default('pending');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });

        $create('employee_profiles', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('applicant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_position_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('Philippines');
            $table->string('nationality')->default('Filipino');
            $table->string('sss_no')->nullable();
            $table->string('philhealth_no')->nullable();
            $table->string('pagibig_no')->nullable();
            $table->string('tin_no')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->date('hire_date')->nullable();
            $table->date('regularization_date')->nullable();
            $table->enum('employment_status', ['probationary', 'regular', 'contractual', 'part_time', 'intern'])->default('probationary');
            $table->string('photo_path')->nullable();
            $table->enum('status', ['active', 'inactive', 'resigned', 'terminated'])->default('active');
            $table->timestamps();
            $table->index(['department_id', 'job_position_id']);
        });

        $create('uploaded_documents', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('applicant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('application_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('document_type');
            $table->string('document_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('verification_notes')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'document_type']);
        });

        $create('notifications', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('message')->nullable();
            $table->json('data')->nullable();
            $table->string('icon')->nullable();
            $table->string('action_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'is_read']);
        });

        $create('activity_logs', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('module')->nullable();
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->index(['entity_type', 'entity_id']);
            $table->index('created_at');
        });

        $create('ai_pipeline_insights', static function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('category')->nullable();
            $table->string('priority')->nullable();
            $table->text('summary')->nullable();
            $table->json('evidence')->nullable();
            $table->text('impact')->nullable();
            $table->text('recommendation')->nullable();
            $table->text('explanation')->nullable();
            $table->text('content');
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('data_signature', 64)->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'activity_logs',
            'notifications',
            'uploaded_documents',
            'employee_profiles',
            'onboarding',
            'onboarding_checklists',
            'offer_letters',
            'ai_recommendations',
            'interview_assessments',
            'interviews',
            'certifications',
            'applicant_skills',
            'applicant_experience',
            'applicant_education',
            'applications',
            'applicants',
            'job_postings',
            'job_positions',
            'departments',
            'role_has_permissions',
            'model_has_roles',
            'model_has_permissions',
            'roles',
            'permissions',
            'personal_access_tokens',
            'failed_jobs',
            'job_batches',
            'jobs',
            'cache_locks',
            'cache',
            'sessions',
            'password_reset_tokens',
            'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
