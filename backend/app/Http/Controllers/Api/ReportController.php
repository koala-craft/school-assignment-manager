<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * 提出状況CSV出力
     */
    public function submissionsCsv(Request $request): StreamedResponse
    {
        $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'assignment_id' => ['nullable', 'exists:assignments,id'],
        ]);

        $user = $request->user();
        $subject = Subject::findOrFail($request->subject_id);

        $this->authorizeReport($user, $subject);

        $query = Submission::query()
            ->with(['assignment', 'student'])
            ->whereHas('assignment', fn ($q) => $q->where('subject_id', $subject->id));

        if ($request->assignment_id) {
            $query->where('assignment_id', $request->assignment_id);
        }

        $submissions = $query->orderBy('assignment_id')->orderBy('student_id')->get();

        $filename = 'submissions_' . date('YmdHis') . '.csv';

        return response()->streamDownload(function () use ($submissions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['氏名', '学籍番号', '課題名', '提出期限', '状態', '提出日時', 'スコア', '採点日時']);
            foreach ($submissions as $s) {
                fputcsv($handle, [
                    $s->student->name ?? '',
                    $s->student->student_number ?? '',
                    $s->assignment->title ?? '',
                    $s->assignment->deadline?->format('Y-m-d H:i') ?? '',
                    $s->status,
                    $s->submitted_at?->format('Y-m-d H:i') ?? '-',
                    $s->score ?? '-',
                    $s->graded_at?->format('Y-m-d H:i') ?? '-',
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * 成績一覧CSV出力
     */
    public function gradesCsv(Request $request): StreamedResponse
    {
        $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
        ]);

        $user = $request->user();
        $subject = Subject::findOrFail($request->subject_id);

        $this->authorizeReport($user, $subject);

        $assignments = Assignment::where('subject_id', $subject->id)
            ->orderBy('deadline')
            ->get();

        $studentIds = $subject->students()->wherePivot('is_active', true)->pluck('users.id');
        $students = User::whereIn('id', $studentIds)->orderBy('student_number')->get();

        $filename = 'grades_' . date('YmdHis') . '.csv';

        return response()->streamDownload(function () use ($students, $assignments, $subject) {
            $handle = fopen('php://output', 'w');
            $header = ['氏名', '学籍番号'];
            foreach ($assignments as $a) {
                $header[] = $a->title . ' (' . $a->deadline->format('m/d') . ')';
            }
            $header[] = '平均点';
            fputcsv($handle, $header);

            foreach ($students as $student) {
                $row = [$student->name, $student->student_number ?? ''];
                $scores = [];
                foreach ($assignments as $assignment) {
                    $sub = Submission::where('assignment_id', $assignment->id)
                        ->where('student_id', $student->id)
                        ->first();
                    $score = $sub && $sub->score !== null ? (string) $sub->score : '-';
                    $row[] = $score;
                    if ($sub && $sub->score !== null) {
                        $scores[] = $sub->score;
                    }
                }
                $avg = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : '-';
                $row[] = $avg;
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * 未提出者リストCSV出力
     */
    public function notSubmittedCsv(Request $request): StreamedResponse
    {
        $request->validate([
            'assignment_id' => ['required', 'exists:assignments,id'],
        ]);

        $assignment = Assignment::with('subject')->findOrFail($request->assignment_id);
        $user = $request->user();

        $this->authorizeReport($user, $assignment->subject);

        $submittedStudentIds = Submission::where('assignment_id', $assignment->id)
            ->whereIn('status', ['submitted', 'graded', 'resubmission_requested'])
            ->pluck('student_id');

        $enrolledStudents = $assignment->subject->students()
            ->wherePivot('is_active', true)
            ->whereNotIn('users.id', $submittedStudentIds)
            ->orderBy('student_number')
            ->get();

        $filename = 'not_submitted_' . date('YmdHis') . '.csv';

        return response()->streamDownload(function () use ($enrolledStudents, $assignment) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['氏名', '学籍番号', 'メールアドレス', '課題名', '提出期限']);
            foreach ($enrolledStudents as $s) {
                fputcsv($handle, [
                    $s->name,
                    $s->student_number ?? '',
                    $s->email,
                    $assignment->title,
                    $assignment->deadline->format('Y-m-d H:i'),
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * 個人成績表PDF出力
     */
    public function studentGradesPdf(Request $request)
    {
        $request->validate([
            'student_id' => ['required', 'exists:users,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
        ]);

        $user = $request->user();
        $student = User::findOrFail($request->student_id);
        $academicYear = AcademicYear::findOrFail($request->academic_year_id);

        if ($user->id !== $student->id && !$user->isAdmin()) {
            abort(403, 'このレポートを閲覧する権限がありません');
        }

        $submissions = Submission::query()
            ->with(['assignment.subject'])
            ->where('student_id', $student->id)
            ->whereHas('assignment.subject', fn ($q) => $q->where('academic_year_id', $request->academic_year_id))
            ->orderBy('assignment_id')
            ->get();

        $scores = $submissions->pluck('score')->filter(fn ($s) => $s !== null);
        $averageScore = $scores->isNotEmpty() ? $scores->avg() : 0;

        $statusLabels = [
            'not_submitted' => '未提出',
            'submitted' => '提出済み',
            'graded' => '採点済み',
            'resubmit_required' => '再提出要',
            'resubmitted' => '再提出済み',
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.student-grades-pdf', [
            'student' => $student,
            'academicYear' => $academicYear,
            'submissions' => $submissions,
            'averageScore' => $averageScore,
            'statusLabels' => $statusLabels,
            'issuedAt' => now()->format('Y年n月j日'),
        ])->setPaper('a4');

        $filename = 'student_grades_' . ($student->student_number ?? $student->id) . '_' . date('YmdHis') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * 個人成績表CSV出力（PDFの代わりにCSVで提供）
     */
    public function studentGradesCsv(Request $request): StreamedResponse
    {
        $request->validate([
            'student_id' => ['required', 'exists:users,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
        ]);

        $user = $request->user();
        $student = User::findOrFail($request->student_id);

        if ($user->id !== $student->id && !$user->isAdmin()) {
            abort(403, 'このレポートを閲覧する権限がありません');
        }

        $submissions = Submission::query()
            ->with(['assignment.subject'])
            ->where('student_id', $student->id)
            ->whereHas('assignment.subject', fn ($q) => $q->where('academic_year_id', $request->academic_year_id))
            ->orderBy('assignment_id')
            ->get();

        $filename = 'student_grades_' . $student->student_number . '_' . date('YmdHis') . '.csv';

        return response()->streamDownload(function () use ($submissions, $student) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['氏名', $student->name]);
            fputcsv($handle, ['学籍番号', $student->student_number ?? '-']);
            fputcsv($handle, []);
            fputcsv($handle, ['科目', '課題名', '提出期限', '状態', '提出日時', 'スコア', '採点日時']);
            foreach ($submissions as $s) {
                fputcsv($handle, [
                    $s->assignment->subject->name ?? '',
                    $s->assignment->title ?? '',
                    $s->assignment->deadline?->format('Y-m-d H:i') ?? '',
                    $s->status,
                    $s->submitted_at?->format('Y-m-d H:i') ?? '-',
                    $s->score ?? '-',
                    $s->graded_at?->format('Y-m-d H:i') ?? '-',
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * レポート閲覧権限チェック
     */
    private function authorizeReport(User $user, Subject $subject): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($user->isTeacher() && $subject->teachers()->where('users.id', $user->id)->exists()) {
            return;
        }

        abort(403, 'このレポートを閲覧する権限がありません');
    }
}
