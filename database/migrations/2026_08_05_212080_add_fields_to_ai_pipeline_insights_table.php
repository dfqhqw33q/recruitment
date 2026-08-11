<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_pipeline_insights', function (Blueprint $table) {
            $table->string('priority')->nullable();
            $table->text('summary')->nullable();
            $table->json('evidence')->nullable();
            $table->text('impact')->nullable();
            $table->text('recommendation')->nullable();
            $table->text('explanation')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ai_pipeline_insights', function (Blueprint $table) {
            $table->dropColumn(['priority', 'summary', 'evidence', 'impact', 'recommendation', 'explanation']);
        });
    }
};
