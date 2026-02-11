<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assignment_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->string('name', 255);
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('grading_type', 20)->default('score');
            $table->integer('max_score')->nullable();
            $table->string('submission_type', 20)->default('file');
            $table->json('allowed_file_types')->nullable();
            $table->bigInteger('max_file_size')->default(52428800);
            $table->integer('max_files')->default(5);
            $table->timestamps();
            
            // Add index
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_templates');
    }
};
