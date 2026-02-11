<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssignmentTemplateRequest;
use App\Http\Requests\UpdateAssignmentTemplateRequest;
use App\Http\Resources\AssignmentTemplateResource;
use App\Models\AssignmentTemplate;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class AssignmentTemplateController extends Controller
{
    /**
     * Display a listing of assignment templates
     */
    public function index(Request $request)
    {
        $query = AssignmentTemplate::query()->withCount('assignments');

        // Filter by creator
        if ($request->has('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        // Search by name or title
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('title', 'ilike', "%{$search}%");
            });
        }

        // Include creator info
        if ($request->boolean('with_creator')) {
            $query->with('creator');
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $templates = $query->paginate($perPage);

        return AssignmentTemplateResource::collection($templates);
    }

    /**
     * Store a newly created template
     */
    public function store(StoreAssignmentTemplateRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $template = AssignmentTemplate::create($data);

        return response()->json([
            'success' => true,
            'message' => 'テンプレートが作成されました',
            'data' => new AssignmentTemplateResource($template),
        ], 201);
    }

    /**
     * Display the specified template
     */
    public function show(string $id)
    {
        $template = AssignmentTemplate::with('creator')
            ->withCount('assignments')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new AssignmentTemplateResource($template),
        ]);
    }

    /**
     * Update the specified template
     */
    public function update(UpdateAssignmentTemplateRequest $request, string $id)
    {
        $template = AssignmentTemplate::findOrFail($id);

        // Check if user is the creator or admin
        if ($template->created_by !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'このテンプレートを編集する権限がありません',
            ], 403);
        }

        $data = $request->validated();
        $oldAttributes = $template->only(array_keys($data));
        $template->update($data);

        AuditLogService::logUpdate($template, $oldAttributes, $template->fresh()->only(array_keys($data)));

        return response()->json([
            'success' => true,
            'message' => 'テンプレートが更新されました',
            'data' => new AssignmentTemplateResource($template),
        ]);
    }

    /**
     * Remove the specified template
     */
    public function destroy(string $id)
    {
        $template = AssignmentTemplate::withCount('assignments')->findOrFail($id);

        // Check if user is the creator or admin
        if ($template->created_by !== request()->user()->id && !request()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'このテンプレートを削除する権限がありません',
            ], 403);
        }

        // Check if template is being used
        if ($template->assignments_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'このテンプレートは使用中のため削除できません',
            ], 400);
        }

        AuditLogService::logDelete($template);
        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'テンプレートが削除されました',
        ]);
    }
}
