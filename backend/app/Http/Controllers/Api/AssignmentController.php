<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssignmentRequest;
use App\Http\Requests\UpdateAssignmentRequest;
use App\Http\Resources\AssignmentResource;
use App\Models\Assignment;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    /**
     * Display a listing of assignments
     */
    public function index(Request $request)
    {
        $query = Assignment::query()->withCount('submissions');

        // Filter by subject
        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by published status
        if ($request->has('is_published')) {
            if ($request->boolean('is_published')) {
                $query->published();
            } else {
                $query->whereNull('published_at');
            }
        }

        // Filter by overdue status
        if ($request->has('is_overdue')) {
            if ($request->boolean('is_overdue')) {
                $query->overdue();
            }
        }

        // Search by title
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('title', 'ilike', "%{$search}%");
        }

        // Include relationships
        if ($request->boolean('with_subject')) {
            $query->with('subject');
        }
        if ($request->boolean('with_template')) {
            $query->with('template');
        }

        // Sort
        $sortBy = $request->input('sort_by', 'deadline');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $assignments = $query->paginate($perPage);

        return AssignmentResource::collection($assignments);
    }

    /**
     * Store a newly created assignment
     */
    public function store(StoreAssignmentRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $data['is_active'] ?? true;

        $assignment = Assignment::create($data);

        AuditLogService::logCreate($assignment);

        return response()->json([
            'success' => true,
            'message' => '課題が作成されました',
            'data' => new AssignmentResource($assignment),
        ], 201);
    }

    /**
     * Display the specified assignment
     */
    public function show(string $id)
    {
        $assignment = Assignment::with(['subject', 'template'])
            ->withCount('submissions')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new AssignmentResource($assignment),
        ]);
    }

    /**
     * Update the specified assignment
     */
    public function update(UpdateAssignmentRequest $request, string $id)
    {
        $assignment = Assignment::findOrFail($id);
        $data = $request->validated();
        $oldAttributes = $assignment->only(array_keys($data));
        $assignment->update($data);

        AuditLogService::logUpdate($assignment, $oldAttributes, $assignment->fresh()->only(array_keys($data)));

        return response()->json([
            'success' => true,
            'message' => '課題が更新されました',
            'data' => new AssignmentResource($assignment),
        ]);
    }

    /**
     * Remove the specified assignment
     */
    public function destroy(string $id)
    {
        $assignment = Assignment::withCount('submissions')->findOrFail($id);

        // Check if there are submitted assignments
        $submittedCount = $assignment->submissions()->submitted()->count();
        if ($submittedCount > 0) {
            return response()->json([
                'success' => false,
                'message' => '提出済みの課題が存在するため削除できません',
            ], 400);
        }

        AuditLogService::logDelete($assignment);
        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => '課題が削除されました',
        ]);
    }

    /**
     * Publish an assignment
     */
    public function publish(string $id)
    {
        $assignment = Assignment::findOrFail($id);

        if ($assignment->published_at) {
            return response()->json([
                'success' => false,
                'message' => 'この課題は既に公開されています',
            ], 400);
        }

        $assignment->update(['published_at' => now()]);

        NotificationService::notifyAssignmentPublished($assignment);

        return response()->json([
            'success' => true,
            'message' => '課題が公開されました',
            'data' => new AssignmentResource($assignment),
        ]);
    }

    /**
     * Unpublish an assignment
     */
    public function unpublish(string $id)
    {
        $assignment = Assignment::findOrFail($id);

        if (!$assignment->published_at) {
            return response()->json([
                'success' => false,
                'message' => 'この課題は公開されていません',
            ], 400);
        }

        // Check if there are submitted assignments
        $submittedCount = $assignment->submissions()->submitted()->count();
        if ($submittedCount > 0) {
            return response()->json([
                'success' => false,
                'message' => '提出済みの課題が存在するため非公開にできません',
            ], 400);
        }

        $assignment->update(['published_at' => null]);

        return response()->json([
            'success' => true,
            'message' => '課題が非公開になりました',
            'data' => new AssignmentResource($assignment),
        ]);
    }

    /**
     * Get submission statistics for an assignment
     */
    public function statistics(string $id)
    {
        $assignment = Assignment::withCount([
            'submissions',
            'submissions as submitted_count' => function ($query) {
                $query->submitted();
            },
            'submissions as graded_count' => function ($query) {
                $query->graded();
            },
            'submissions as overdue_count' => function ($query) {
                $query->where('is_overdue', true);
            },
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'assignment' => new AssignmentResource($assignment),
                'statistics' => [
                    'total_submissions' => $assignment->submissions_count,
                    'submitted' => $assignment->submitted_count,
                    'graded' => $assignment->graded_count,
                    'overdue' => $assignment->overdue_count,
                    'pending' => $assignment->submissions_count - $assignment->submitted_count,
                ],
            ],
        ]);
    }
}
