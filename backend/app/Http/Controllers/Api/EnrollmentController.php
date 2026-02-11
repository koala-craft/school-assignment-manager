<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EnrollmentResource;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of enrollments
     */
    public function index(Request $request)
    {
        $query = Enrollment::query();

        // Filter by subject
        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by student
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Include relations
        if ($request->boolean('with_subject')) {
            $query->with('subject');
        }
        if ($request->boolean('with_student')) {
            $query->with('student');
        }

        // Sort
        $sortBy = $request->input('sort_by', 'enrolled_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $enrollments = $query->paginate($perPage);

        return EnrollmentResource::collection($enrollments);
    }

    /**
     * Enroll students in a subject
     */
    public function enrollStudents(Request $request, string $subjectId)
    {
        $request->validate([
            'student_ids' => ['required', 'array'],
            'student_ids.*' => ['exists:users,id'],
        ]);

        $subject = Subject::findOrFail($subjectId);

        // Verify all users are students
        $students = User::whereIn('id', $request->student_ids)
            ->whereIn('role', ['student', 'student_admin'])
            ->pluck('id');

        if ($students->count() !== count($request->student_ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Some users are not students',
            ], 400);
        }

        $enrolled = [];
        $alreadyEnrolled = [];

        foreach ($request->student_ids as $studentId) {
            // Check if already enrolled
            $existing = Enrollment::where('subject_id', $subjectId)
                ->where('student_id', $studentId)
                ->first();

            if ($existing) {
                $alreadyEnrolled[] = $studentId;
            } else {
                $enrollment = Enrollment::create([
                    'subject_id' => $subjectId,
                    'student_id' => $studentId,
                    'enrolled_at' => now(),
                    'is_active' => true,
                ]);
                $enrolled[] = $enrollment;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Students enrolled successfully',
            'data' => [
                'enrolled_count' => count($enrolled),
                'already_enrolled_count' => count($alreadyEnrolled),
                'enrollments' => EnrollmentResource::collection($enrolled),
            ],
        ], 201);
    }

    /**
     * Remove student enrollment
     */
    public function unenrollStudent(string $subjectId, string $studentId)
    {
        $enrollment = Enrollment::where('subject_id', $subjectId)
            ->where('student_id', $studentId)
            ->firstOrFail();

        $enrollment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student unenrolled successfully',
        ]);
    }

    /**
     * Toggle enrollment active status
     */
    public function toggleActive(string $id)
    {
        $enrollment = Enrollment::findOrFail($id);
        $enrollment->update(['is_active' => !$enrollment->is_active]);

        return response()->json([
            'success' => true,
            'message' => $enrollment->is_active ? 'Enrollment activated' : 'Enrollment deactivated',
            'data' => new EnrollmentResource($enrollment),
        ]);
    }

    /**
     * Bulk enroll students to multiple subjects
     */
    public function bulkEnroll(Request $request)
    {
        $request->validate([
            'subject_ids' => ['required', 'array'],
            'subject_ids.*' => ['exists:subjects,id'],
            'student_ids' => ['required', 'array'],
            'student_ids.*' => ['exists:users,id'],
        ]);

        // Verify all users are students
        $students = User::whereIn('id', $request->student_ids)
            ->whereIn('role', ['student', 'student_admin'])
            ->pluck('id');

        if ($students->count() !== count($request->student_ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Some users are not students',
            ], 400);
        }

        $enrolled = 0;
        $skipped = 0;

        foreach ($request->subject_ids as $subjectId) {
            foreach ($request->student_ids as $studentId) {
                $existing = Enrollment::where('subject_id', $subjectId)
                    ->where('student_id', $studentId)
                    ->exists();

                if (!$existing) {
                    Enrollment::create([
                        'subject_id' => $subjectId,
                        'student_id' => $studentId,
                        'enrolled_at' => now(),
                        'is_active' => true,
                    ]);
                    $enrolled++;
                } else {
                    $skipped++;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Bulk enrollment completed',
            'data' => [
                'enrolled_count' => $enrolled,
                'skipped_count' => $skipped,
            ],
        ], 201);
    }
}
