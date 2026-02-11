<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTermRequest;
use App\Http\Requests\UpdateTermRequest;
use App\Http\Resources\TermResource;
use App\Models\Term;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class TermController extends Controller
{
    /**
     * Display a listing of terms
     */
    public function index(Request $request)
    {
        $query = Term::query()->withCount('subjects');

        // Filter by academic year
        if ($request->has('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Include academic year
        if ($request->boolean('with_academic_year')) {
            $query->with('academicYear');
        }

        // Sort
        $sortBy = $request->input('sort_by', 'start_date');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $terms = $query->paginate($perPage);

        return TermResource::collection($terms);
    }

    /**
     * Store a newly created term
     */
    public function store(StoreTermRequest $request)
    {
        $term = Term::create($request->validated());

        AuditLogService::logCreate($term);

        return response()->json([
            'success' => true,
            'message' => 'Term created successfully',
            'data' => new TermResource($term),
        ], 201);
    }

    /**
     * Display the specified term
     */
    public function show(string $id)
    {
        $term = Term::with('academicYear')
            ->withCount('subjects')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new TermResource($term),
        ]);
    }

    /**
     * Update the specified term
     */
    public function update(UpdateTermRequest $request, string $id)
    {
        $term = Term::findOrFail($id);
        $term->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Term updated successfully',
            'data' => new TermResource($term),
        ]);
    }

    /**
     * Remove the specified term
     */
    public function destroy(string $id)
    {
        $term = Term::withCount('subjects')->findOrFail($id);

        // Check if term has subjects
        if ($term->subjects_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete term with existing subjects',
            ], 400);
        }

        AuditLogService::logDelete($term);
        $term->delete();

        return response()->json([
            'success' => true,
            'message' => 'Term deleted successfully',
        ]);
    }
}
