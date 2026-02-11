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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->onDelete('restrict');
            $table->foreignId('student_id')->constrained('users')->onDelete('restrict');
            $table->string('status', 20)->default('not_submitted');
            $table->integer('score')->nullable();
            $table->string('grade', 10)->nullable();
            $table->text('teacher_comments')->nullable();
            $table->text('student_comments')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resubmission_deadline')->nullable();
            $table->integer('resubmission_count')->default(0);
            $table->boolean('is_overdue')->default(false);
            $table->timestamps();
            
            // Add unique constraint and indexes
            $table->unique(['assignment_id', 'student_id']);
            $table->index('student_id');
            $table->index('status');
            $table->index('submitted_at');
            $table->index('graded_at');
            $table->index('is_overdue');
        });
        
        // Add CHECK constraints
        DB::statement("ALTER TABLE submissions ADD CONSTRAINT chk_submissions_status CHECK (status IN ('not_submitted', 'submitted', 'graded', 'resubmission_requested'))");
        DB::statement("ALTER TABLE submissions ADD CONSTRAINT chk_submissions_score CHECK (score IS NULL OR score >= 0)");
        DB::statement("ALTER TABLE submissions ADD CONSTRAINT chk_submissions_resubmission_count CHECK (resubmission_count >= 0)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE submissions DROP CONSTRAINT IF EXISTS chk_submissions_status");
        DB::statement("ALTER TABLE submissions DROP CONSTRAINT IF EXISTS chk_submissions_score");
        DB::statement("ALTER TABLE submissions DROP CONSTRAINT IF EXISTS chk_submissions_resubmission_count");
        Schema::dropIfExists('submissions');
    }
};
