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
        Schema::table('submissions', function (Blueprint $table) {
            // student_id + status の複合インデックス（学生の提出状況一覧で使用）
            $table->index(['student_id', 'status'], 'idx_submissions_student_status');
            
            // assignment_id + status の複合インデックス（課題ごとの提出状況で使用）
            $table->index(['assignment_id', 'status'], 'idx_submissions_assignment_status');
        });

        Schema::table('enrollments', function (Blueprint $table) {
            // subject_id + is_active の複合インデックス（科目ごとのアクティブ学生数で使用）
            $table->index(['subject_id', 'is_active'], 'idx_enrollments_subject_active');
        });

        Schema::table('assignments', function (Blueprint $table) {
            // subject_id + is_active + published_at の複合インデックス（ダッシュボードで使用）
            $table->index(['subject_id', 'is_active', 'published_at'], 'idx_assignments_subject_active_published');
            
            // subject_id + deadline の複合インデックス（締切順ソートで使用）
            $table->index(['subject_id', 'deadline'], 'idx_assignments_subject_deadline');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            // user_id + created_at の複合インデックス（ユーザーごとの監査ログで使用）
            $table->index(['user_id', 'created_at'], 'idx_audit_logs_user_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex('idx_submissions_student_status');
            $table->dropIndex('idx_submissions_assignment_status');
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex('idx_enrollments_subject_active');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex('idx_assignments_subject_active_published');
            $table->dropIndex('idx_assignments_subject_deadline');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_logs_user_created');
        });
    }
};
