<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('reference_code', 30)->nullable()->unique();
            $table->string('custom_resume_path')->nullable();
            $table->text('custom_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['reference_code', 'custom_resume_path', 'custom_notes']);
        });
    }
};
