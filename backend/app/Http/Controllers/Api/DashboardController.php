<?php

namespace App\Http\Controllers\Api;

use App\Helpers\QueryPerformanceHelper;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * 管理者ダッシュボード
     */
    public function admin(Request $request)
    {
        // パフォーマンス測定（開発環境のみ）
        $startTime = microtime(true);
        
        $user = $request->user();
        if (!$user->isAdmin()) {
            abort(403, '管理者のみアクセス可能です');
        }

        $totalUsers = User::withTrashed()->count();
        $totalSubjects = Subject::withTrashed()->count();
        $totalAssignments = Assignment::withTrashed()->count();
        $activeStudents = User::students()->active()->count();

        $submissionStats = $this->getSubmissionStats();

        $recentActivities = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                $createdAt = $log->created_at;
                if ($createdAt && !($createdAt instanceof \Carbon\Carbon)) {
                    $createdAt = Carbon::parse($createdAt);
                }
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'model' => $log->model,
                    'user_name' => $log->user?->name,
                    'created_at' => $createdAt?->toIso8601String(),
                ];
            });

        // 開発環境でのパフォーマンスログ
        if (app()->environment(['local', 'testing'])) {
            $totalTime = microtime(true) - $startTime;
            \Illuminate\Support\Facades\Log::info('Dashboard Admin Performance', [
                'total_ms' => round($totalTime * 1000, 2),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => $totalUsers,
                'total_subjects' => $totalSubjects,
                'total_assignments' => $totalAssignments,
                'active_students' => $activeStudents,
                'recent_activities' => $recentActivities,
                'submission_stats' => $submissionStats,
            ],
        ]);
    }

    /**
     * 教員ダッシュボード
     */
    public function teacher(Request $request)
    {
        $user = $request->user();
        if (!$user->isTeacher() && !$user->isAdmin()) {
            abort(403, '教員のみアクセス可能です');
        }

        // taughtSubjects() は subjects と subject_teachers をJOINするため
        // pluck('id') だと「id が曖昧」となり 500 エラーになる。
        // 明示的に subjects.id を指定して取得する。
        $subjectIds = $user->taughtSubjects()->pluck('subjects.id');
        $mySubjects = $subjectIds->count();

        // 改善: pluck()->unique()->count() → distinct()->count() に変更
        $totalStudents = Enrollment::whereIn('subject_id', $subjectIds)
            ->where('is_active', true)
            ->distinct('student_id')
            ->count('student_id');

        $assignments = Assignment::whereIn('subject_id', $subjectIds)->active();
        $totalAssignments = (clone $assignments)->count();

        // 改善: whereHas → JOIN に変更
        $assignmentIds = Assignment::whereIn('subject_id', $subjectIds)->pluck('id');
        $pendingGrading = Submission::needsGrading()
            ->whereIn('assignment_id', $assignmentIds)
            ->count();

        // 改善: whereHas → JOIN に変更
        $recentSubmissions = Submission::with(['assignment.subject', 'student'])
            ->whereIn('assignment_id', $assignmentIds)
            ->whereIn('status', ['submitted', 'resubmitted'])
            ->orderBy('submitted_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($s) {
                $submittedAt = $s->submitted_at;
                if ($submittedAt && !($submittedAt instanceof \Carbon\Carbon)) {
                    $submittedAt = Carbon::parse($submittedAt);
                }
                return [
                    'id' => $s->id,
                    'assignment_title' => $s->assignment?->title,
                    'subject_name' => $s->assignment?->subject?->name,
                    'student_name' => $s->student?->name,
                    'submitted_at' => $submittedAt?->toIso8601String(),
                ];
            });

        $upcomingDeadlines = Assignment::with('subject')
            ->whereIn('subject_id', $subjectIds)
            ->active()
            ->where('deadline', '>', now())
            ->whereNotNull('published_at')
            ->orderBy('deadline')
            ->limit(5)
            ->get()
            ->map(function ($a) {
                $deadline = $a->deadline;
                if ($deadline && !($deadline instanceof \Carbon\Carbon)) {
                    $deadline = Carbon::parse($deadline);
                }
                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'subject_name' => $a->subject?->name,
                    'deadline' => $deadline?->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'my_subjects' => $mySubjects,
                'total_students' => $totalStudents,
                'total_assignments' => $totalAssignments,
                'pending_grading' => $pendingGrading,
                'recent_submissions' => $recentSubmissions,
                'upcoming_deadlines' => $upcomingDeadlines,
            ],
        ]);
    }

    /**
     * 学生ダッシュボード
     */
    public function student(Request $request)
    {
        $user = $request->user();
        if (!$user->isStudent() && !$user->isStudentAdmin() && !$user->isAdmin()) {
            abort(403, '学生のみアクセス可能です');
        }

        $subjectIds = $user->enrolledSubjects()->wherePivot('is_active', true)->pluck('id');
        $enrolledSubjects = $subjectIds->count();

        $assignments = Assignment::whereIn('subject_id', $subjectIds)
            ->active()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        $totalAssignments = (clone $assignments)->count();

        // 改善: whereHas → JOIN に変更
        $assignmentIds = Assignment::whereIn('subject_id', $subjectIds)
            ->active()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->pluck('id');

        $submissions = Submission::where('student_id', $user->id)
            ->whereIn('assignment_id', $assignmentIds);

        $notSubmitted = (clone $submissions)->notSubmitted()->count();
        $graded = (clone $submissions)->graded()->count();

        $upcomingDeadlines = Assignment::with('subject')
            ->whereIn('subject_id', $subjectIds)
            ->active()
            ->where('deadline', '>', now())
            ->whereNotNull('published_at')
            ->orderBy('deadline')
            ->limit(5)
            ->get()
            ->map(function ($a) {
                $deadline = $a->deadline;
                if ($deadline && !($deadline instanceof \Carbon\Carbon)) {
                    $deadline = Carbon::parse($deadline);
                }
                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'subject_name' => $a->subject?->name,
                    'deadline' => $deadline?->toIso8601String(),
                ];
            });

        $recentGrades = Submission::with(['assignment.subject'])
            ->where('student_id', $user->id)
            ->graded()
            ->orderBy('graded_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($s) {
                $gradedAt = $s->graded_at;
                if ($gradedAt && !($gradedAt instanceof \Carbon\Carbon)) {
                    $gradedAt = Carbon::parse($gradedAt);
                }
                return [
                    'id' => $s->id,
                    'assignment_title' => $s->assignment?->title,
                    'subject_name' => $s->assignment?->subject?->name,
                    'score' => $s->score,
                    'graded_at' => $gradedAt?->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'enrolled_subjects' => $enrolledSubjects,
                'total_assignments' => $totalAssignments,
                'not_submitted' => $notSubmitted,
                'graded' => $graded,
                'upcoming_deadlines' => $upcomingDeadlines,
                'recent_grades' => $recentGrades,
            ],
        ]);
    }

    private function getSubmissionStats(): array
    {
        // 改善: 複数クエリ → 1つの集約クエリに変更
        $stats = Submission::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status IN (\'submitted\', \'graded\', \'resubmit_required\', \'resubmitted\') THEN 1 ELSE 0 END) as submitted,
            SUM(CASE WHEN is_overdue = true THEN 1 ELSE 0 END) as overdue
        ')->first();

        return [
            'total' => (int) $stats->total,
            'submitted' => (int) $stats->submitted,
            'not_submitted' => (int) ($stats->total - $stats->submitted),
            'overdue' => (int) $stats->overdue,
        ];
    }
}
