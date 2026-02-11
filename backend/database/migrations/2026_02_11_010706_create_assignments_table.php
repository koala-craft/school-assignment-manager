<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('restrict');
            $table->foreignId('template_id')->nullable()->constrained('assignment_templates')->onDelete('set null');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->timestamp('deadline');
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_graded')->default(true);
            $table->string('grading_type', 20)->default('points');
            $table->integer('max_score')->nullable();
            $table->string('submission_type', 20)->default('file');
            $table->json('allowed_file_types')->nullable();
            $table->bigInteger('max_file_size')->default(52428800);
            $table->integer('max_files')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Add indexes
            $table->index('subject_id');
            $table->index('template_id');
            $table->index('deadline');
            $table->index('published_at');
            $table->index('is_active');
            $table->index('deleted_at');
        });
        
        // Add CHECK constraints
        DB::statement("ALTER TABLE assignments ADD CONSTRAINT chk_assignments_grading_type CHECK (grading_type IN ('points', 'letter', 'pass_fail'))");
        DB::statement("ALTER TABLE assignments ADD CONSTRAINT chk_assignments_submission_type CHECK (submission_type IN ('file', 'text', 'both', 'none'))");
        DB::statement("ALTER TABLE assignments ADD CONSTRAINT chk_assignments_max_score CHECK (max_score IS NULL OR max_score > 0)");
        DB::statement("ALTER TABLE assignments ADD CONSTRAINT chk_assignments_max_file_size CHECK (max_file_size > 0)");
        DB::statement("ALTER TABLE assignments ADD CONSTRAINT chk_assignments_max_files CHECK (max_files > 0)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE assignments DROP CONSTRAINT IF EXISTS chk_assignments_grading_type");
        DB::statement("ALTER TABLE assignments DROP CONSTRAINT IF EXISTS chk_assignments_submission_type");
        DB::statement("ALTER TABLE assignments DROP CONSTRAINT IF EXISTS chk_assignments_max_score");
        DB::statement("ALTER TABLE assignments DROP CONSTRAINT IF EXISTS chk_assignments_max_file_size");
        DB::statement("ALTER TABLE assignments DROP CONSTRAINT IF EXISTS chk_assignments_max_files");
        Schema::dropIfExists('assignments');
    }
};
