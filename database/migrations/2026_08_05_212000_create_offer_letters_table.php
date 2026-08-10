<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_posting_id')->constrained()->onDelete('cascade');
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
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_letters');
    }
};
