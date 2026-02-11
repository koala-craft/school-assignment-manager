<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Http\Resources\SubjectResource;
use App\Models\Subject;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects
     */
    public function index(Request $request)
    {
        $query = Subject::query()
            ->withCount(['teachers', 'students', 'assignments']);

        // Filter by academic year
        if ($request->has('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Filter by term
        if ($request->has('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search by code or name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'ilike', "%{$search}%")
                  ->orWhere('name', 'ilike', "%{$search}%");
            });
        }

        // Include relations
        if ($request->boolean('with_academic_year')) {
            $query->with('academicYear');
        }
        if ($request->boolean('with_term')) {
            $query->with('term');
        }
        if ($request->boolean('with_teachers')) {
            $query->with('teachers');
        }
        if ($request->boolean('with_students')) {
            $query->with('students');
        }

        // Sort
        $sortBy = $request->input('sort_by', 'code');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $subjects = $query->paginate($perPage);

        return SubjectResource::collection($subjects);
    }

    /**
     * Display a listing of subjects for the authenticated teacher
     */
    public function indexForTeacher(Request $request)
    {
        $user = $request->user();

        if (!$user->isTeacher() && !$user->isAdmin()) {
            abort(403, '教員のみアクセス可能です');
        }

        $query = Subject::query()
            ->whereHas('teachers', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });

        // Include relations needed for教員画面の科目管理一覧
        $query->with(['academicYear', 'term', 'teachers']);

        // Optionally allow filtering / search in the future

        $perPage = $request->input('per_page', 50);
        $subjects = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $subjects->items(),
        ]);
    }

    /**
     * Store a newly created subject
     */
    public function store(StoreSubjectRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $data['is_active'] ?? true;

        $subject = Subject::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Subject created successfully',
            'data' => new SubjectResource($subject),
        ], 201);
    }

    /**
     * Display the specified subject
     */
    public function show(string $id)
    {
        $subject = Subject::with(['academicYear', 'term', 'teachers', 'students'])
            ->withCount(['teachers', 'students', 'assignments'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new SubjectResource($subject),
        ]);
    }

    /**
     * Update the specified subject
     */
    public function update(UpdateSubjectRequest $request, string $id)
    {
        $subject = Subject::findOrFail($id);
        $data = $request->validated();
        $oldAttributes = $subject->only(array_keys($data));
        $subject->update($data);

        AuditLogService::logUpdate($subject, $oldAttributes, $subject->fresh()->only(array_keys($data)));

        return response()->json([
            'success' => true,
            'message' => 'Subject updated successfully',
            'data' => new SubjectResource($subject),
        ]);
    }

    /**
     * Remove the specified subject
     */
    public function destroy(string $id)
    {
        $subject = Subject::withCount(['students', 'assignments'])->findOrFail($id);

        // Check if subject has enrollments or assignments
        if ($subject->students_count > 0 || $subject->assignments_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete subject with existing enrollments or assignments',
            ], 400);
        }

        AuditLogService::logDelete($subject);
        $subject->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subject deleted successfully',
        ]);
    }

    /**
     * Assign teachers to subject
     */
    public function assignTeachers(Request $request, string $id)
    {
        $request->validate([
            'teacher_ids' => ['required', 'array'],
            'teacher_ids.*' => ['exists:users,id'],
        ]);

        $subject = Subject::findOrFail($id);

        // Verify all users are teachers
        $teachers = User::whereIn('id', $request->teacher_ids)
            ->where('role', 'teacher')
            ->pluck('id');

        if ($teachers->count() !== count($request->teacher_ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Some users are not teachers',
            ], 400);
        }

        $subject->teachers()->sync($request->teacher_ids);

        return response()->json([
            'success' => true,
            'message' => 'Teachers assigned successfully',
            'data' => new SubjectResource($subject->load('teachers')),
        ]);
    }

    /**
     * Remove teacher from subject
     */
    public function removeTeacher(string $id, string $teacherId)
    {
        $subject = Subject::findOrFail($id);
        $subject->teachers()->detach($teacherId);

        return response()->json([
            'success' => true,
            'message' => 'Teacher removed successfully',
        ]);
    }

    /**
     * Get enrolled students
     */
    public function students(string $id)
    {
        $subject = Subject::with('students')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'subject' => new SubjectResource($subject),
                'students' => $subject->students,
            ],
        ]);
    }
}
