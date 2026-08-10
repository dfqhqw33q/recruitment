<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_id')->constrained()->onDelete('cascade');
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
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_assessments');
    }
};
