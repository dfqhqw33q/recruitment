<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_pipeline_insights', function (Blueprint $table) {
            $table->string('priority')->nullable()->after('category');
            $table->text('summary')->nullable()->after('priority');
            $table->json('evidence')->nullable()->after('summary');
            $table->text('impact')->nullable()->after('evidence');
            $table->text('recommendation')->nullable()->after('impact');
            $table->text('explanation')->nullable()->after('recommendation');
        });
    }

    public function down(): void
    {
        Schema::table('ai_pipeline_insights', function (Blueprint $table) {
            $table->dropColumn(['priority', 'summary', 'evidence', 'impact', 'recommendation', 'explanation']);
        });
    }
};
