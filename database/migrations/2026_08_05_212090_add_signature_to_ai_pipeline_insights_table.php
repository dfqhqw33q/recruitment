<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_pipeline_insights', function (Blueprint $table) {
            $table->string('data_signature', 64)->nullable()->after('sort_order');
            $table->timestamp('generated_at')->nullable()->after('data_signature');
        });
    }

public function down(): void
    {
        Schema::table('ai_pipeline_insights', function (Blueprint $table) {
            $table->dropColumn(['data_signature', 'generated_at']);
        });
    }
};

