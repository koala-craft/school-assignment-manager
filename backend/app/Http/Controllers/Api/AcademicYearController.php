<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAcademicYearRequest;
use App\Http\Requests\UpdateAcademicYearRequest;
use App\Http\Resources\AcademicYearResource;
use App\Models\AcademicYear;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of academic years
     */
    public function index(Request $request)
    {
        $query = AcademicYear::query()->withCount('subjects');

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Include terms
        if ($request->boolean('with_terms')) {
            $query->with('terms');
        }

        // Sort
        $sortBy = $request->input('sort_by', 'year');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $academicYears = $query->paginate($perPage);

        return AcademicYearResource::collection($academicYears);
    }

    /**
     * Store a newly created academic year
     */
    public function store(StoreAcademicYearRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $data['is_active'] ?? false;

        $academicYear = AcademicYear::create($data);

        AuditLogService::logCreate($academicYear);

        return response()->json([
            'success' => true,
            'message' => 'Academic year created successfully',
            'data' => new AcademicYearResource($academicYear),
        ], 201);
    }

    /**
     * Display the specified academic year
     */
    public function show(string $id)
    {
        $academicYear = AcademicYear::with('terms')
            ->withCount('subjects')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new AcademicYearResource($academicYear),
        ]);
    }

    /**
     * Update the specified academic year
     */
    public function update(UpdateAcademicYearRequest $request, string $id)
    {
        $academicYear = AcademicYear::findOrFail($id);
        $academicYear->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Academic year updated successfully',
            'data' => new AcademicYearResource($academicYear),
        ]);
    }

    /**
     * Remove the specified academic year
     */
    public function destroy(string $id)
    {
        $academicYear = AcademicYear::withCount('subjects')->findOrFail($id);

        // Check if academic year has subjects
        if ($academicYear->subjects_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete academic year with existing subjects',
            ], 400);
        }

        AuditLogService::logDelete($academicYear);
        $academicYear->delete();

        return response()->json([
            'success' => true,
            'message' => 'Academic year deleted successfully',
        ]);
    }

    /**
     * Set academic year as active
     */
    public function setActive(string $id)
    {
        // Deactivate all academic years
        AcademicYear::query()->update(['is_active' => false]);

        // Activate the specified academic year
        $academicYear = AcademicYear::findOrFail($id);
        $academicYear->update(['is_active' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Academic year set as active',
            'data' => new AcademicYearResource($academicYear),
        ]);
    }

    /**
     * Get current active academic year
     */
    public function current()
    {
        $academicYear = AcademicYear::active()
            ->with('terms')
            ->withCount('subjects')
            ->first();

        if (!$academicYear) {
            return response()->json([
                'success' => false,
                'message' => 'No active academic year found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new AcademicYearResource($academicYear),
        ]);
    }
}
