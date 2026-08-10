<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_position_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
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
    }

    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
