<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->json('screening_questions')->nullable()->after('qualifications');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->json('screening_answers')->nullable()->after('custom_notes');
            $table->boolean('is_knocked_out')->default(false)->after('screening_answers');
            $table->string('knockout_reason')->nullable()->after('is_knocked_out');
        });
    }

    public function down(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropColumn('screening_questions');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['screening_answers', 'is_knocked_out', 'knockout_reason']);
        });
    }
};
