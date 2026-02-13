<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubmissionResource;
use App\Models\Assignment;
use App\Models\Submission;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubmissionController extends Controller
{
    /**
     * Display a listing of submissions
     */
    public function index(Request $request)
    {
        $query = Submission::query();

        // Scope by user role when not admin
        if (!$request->user()->isAdmin()) {
            if ($request->user()->isTeacher()) {
                // taughtSubjects() は subjects と subject_teachers をJOINするため
                // pluck('id') だと「id が曖昧」となりエラーになる。
                // 明示的に subjects.id を指定して取得する。
                $subjectIds = $request->user()->taughtSubjects()->pluck('subjects.id');
                $assignmentIds = \App\Models\Assignment::whereIn('subject_id', $subjectIds)->pluck('id');
                $query->whereIn('assignment_id', $assignmentIds);
            } else {
                $query->where('student_id', $request->user()->id);
            }
        }

        // Filter by assignment
        if ($request->has('assignment_id')) {
            $query->where('assignment_id', $request->assignment_id);
        }

        // Filter by student
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by submitted status
        if ($request->has('is_submitted')) {
            if ($request->boolean('is_submitted')) {
                $query->submitted();
            } else {
                $query->where('status', 'not_submitted');
            }
        }

        // Filter by graded status
        if ($request->has('is_graded')) {
            if ($request->boolean('is_graded')) {
                $query->graded();
            } else {
                $query->whereNull('graded_at');
            }
        }

        // Include relationships
        if ($request->boolean('with_assignment')) {
            $query->with('assignment.subject');
        }
        if ($request->boolean('with_student')) {
            $query->with('student');
        }
        if ($request->boolean('with_grader')) {
            $query->with('grader');
        }

        // Sort
        $sortBy = $request->input('sort_by', 'submitted_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $submissions = $query->paginate($perPage);

        return SubmissionResource::collection($submissions);
    }

    /**
     * Submit an assignment
     */
    public function submit(Request $request, string $assignmentId)
    {
        $request->validate([
            'student_comments' => ['nullable', 'string'],
        ]);

        $assignment = Assignment::findOrFail($assignmentId);

        // Check if assignment is published
        if (!$assignment->isPublished) {
            return response()->json([
                'success' => false,
                'message' => 'この課題は公開されていません',
            ], 400);
        }

        // Get or create submission
        $submission = Submission::firstOrCreate(
            [
                'assignment_id' => $assignmentId,
                'student_id' => $request->user()->id,
            ],
            [
                'status' => 'not_submitted',
            ]
        );

        // Check if already submitted
        if ($submission->isSubmitted && $submission->status !== 'resubmission_requested') {
            return response()->json([
                'success' => false,
                'message' => '既に提出済みです',
            ], 400);
        }

        // Update submission
        $isOverdue = now()->isAfter($assignment->deadline);
        $submission->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'student_comments' => $request->student_comments,
            'is_overdue' => $isOverdue,
            'resubmission_count' => $submission->status === 'resubmission_requested' 
                ? $submission->resubmission_count + 1 
                : 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => '課題を提出しました',
            'data' => new SubmissionResource($submission),
        ], 201);
    }

    /**
     * Grade a submission
     */
    public function grade(Request $request, string $id)
    {
        // 教員または管理者のみ採点可能
        $user = $request->user();
        if (!$user->isTeacher() && !$user->isAdmin()) {
            abort(403, '採点は教員または管理者のみ可能です');
        }

        $request->validate([
            'score' => ['nullable', 'numeric', 'min:0'],
            'grade' => ['nullable', 'string', 'max:10'],
            'teacher_comments' => ['nullable', 'string'],
            'request_resubmission' => ['boolean'],
            'resubmission_deadline' => ['nullable', 'date', 'after:now'],
        ]);

        $submission = Submission::with('assignment')->findOrFail($id);

        // Check if submission is submitted
        if (!$submission->isSubmitted) {
            return response()->json([
                'success' => false,
                'message' => '未提出の課題には採点できません',
            ], 400);
        }

        // Validate score against assignment max_score
        if ($request->has('score')) {
            $assignment = $submission->assignment;
            if ($request->score > $assignment->max_score) {
                return response()->json([
                    'success' => false,
                    'message' => "スコアは{$assignment->max_score}点以下にしてください",
                ], 400);
            }
        }

        // Update submission
        $data = [
            'score' => $request->score,
            'grade' => $request->grade,
            'teacher_comments' => $request->teacher_comments,
            'graded_at' => now(),
            'graded_by' => $request->user()->id,
        ];

        if ($request->boolean('request_resubmission')) {
            $data['status'] = 'resubmission_requested';
            $data['resubmission_deadline'] = $request->resubmission_deadline;
        } else {
            $data['status'] = 'graded';
        }

        $submission->update($data);

        if ($data['status'] === 'graded') {
            NotificationService::notifySubmissionGraded($submission);
        } elseif ($data['status'] === 'resubmission_requested') {
            NotificationService::notifyResubmissionRequired($submission);
        }

        return response()->json([
            'success' => true,
            'message' => '採点が完了しました',
            'data' => new SubmissionResource($submission->load(['assignment', 'student', 'grader'])),
        ]);
    }

    /**
     * Display the specified submission
     */
    public function show(string $id)
    {
        $submission = Submission::with(['assignment.subject', 'student', 'grader'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new SubmissionResource($submission),
        ]);
    }

    /**
     * Delete a submission
     */
    public function destroy(string $id)
    {
        $submission = Submission::findOrFail($id);

        // Only allow deletion if not submitted or if user is admin
        if ($submission->isSubmitted && !request()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => '提出済みの課題は削除できません',
            ], 400);
        }

        $submission->delete();

        return response()->json([
            'success' => true,
            'message' => '提出を削除しました',
        ]);
    }

    /**
     * Bulk create submissions for all enrolled students
     */
    public function bulkCreate(Request $request, string $assignmentId)
    {
        $assignment = Assignment::with('subject.students')->findOrFail($assignmentId);

        // 改善: ループ内クエリを一括処理に変更
        $studentIds = $assignment->subject->students->pluck('id');
        $existingSubmissions = Submission::where('assignment_id', $assignmentId)
            ->whereIn('student_id', $studentIds)
            ->pluck('student_id')
            ->toArray();

        $newStudentIds = $studentIds->diff($existingSubmissions);
        $createdCount = 0;
        $existingCount = count($existingSubmissions);

        // 一括挿入
        if ($newStudentIds->isNotEmpty()) {
            $insertData = $newStudentIds->map(function ($studentId) use ($assignmentId) {
                return [
                    'assignment_id' => $assignmentId,
                    'student_id' => $studentId,
                    'status' => 'not_submitted',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            Submission::insert($insertData);
            $createdCount = count($insertData);
        }

        return response()->json([
            'success' => true,
            'message' => '提出レコードを一括作成しました',
            'data' => [
                'created_count' => $createdCount,
                'existing_count' => $existingCount,
                'total_students' => $assignment->subject->students->count(),
            ],
        ], 201);
    }

    /**
     * Get submission statistics by assignment
     */
    public function statisticsByAssignment(string $assignmentId)
    {
        $assignment = Assignment::findOrFail($assignmentId);

        $statistics = DB::table('submissions')
            ->where('assignment_id', $assignmentId)
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status != \'not_submitted\' THEN 1 ELSE 0 END) as submitted'),
                DB::raw('SUM(CASE WHEN graded_at IS NOT NULL THEN 1 ELSE 0 END) as graded'),
                DB::raw('SUM(CASE WHEN is_overdue = true THEN 1 ELSE 0 END) as overdue'),
                DB::raw('AVG(CASE WHEN score IS NOT NULL THEN score ELSE NULL END) as avg_score')
            )
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'assignment_id' => $assignmentId,
                'statistics' => $statistics,
            ],
        ]);
    }
}
