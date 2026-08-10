<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_posting_id')->constrained()->onDelete('cascade');
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
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_recommendations');
    }
};
