<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users with filtering and pagination
     */
    public function index(Request $request)
    {
        $query = User::query()->withTrashed();

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('student_number', 'ilike', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $users = $query->paginate($perPage);

        return UserResource::collection($users);
    }

    /**
     * Store a newly created user
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $data['is_active'] ?? true;

        $user = User::create($data);

        AuditLogService::logCreate($user);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => new UserResource($user),
        ], 201);
    }

    /**
     * Display the specified user
     */
    public function show(string $id)
    {
        $user = User::withTrashed()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(UpdateUserRequest $request, string $id)
    {
        $user = User::withTrashed()->findOrFail($id);
        
        $data = $request->validated();
        $logKeys = array_diff(array_keys($data), ['password']);
        $oldAttributes = $user->only($logKeys);
        
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        AuditLogService::logUpdate($user, $oldAttributes, $user->fresh()->only($logKeys));

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Remove the specified user (soft delete)
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        // Prevent self-deletion
        if ($user->id === request()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account',
            ], 400);
        }

        AuditLogService::logDelete($user);
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Restore a soft-deleted user
     */
    public function restore(string $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return response()->json([
            'success' => true,
            'message' => 'User restored successfully',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Permanently delete a user
     */
    public function forceDestroy(string $id)
    {
        $user = User::withTrashed()->findOrFail($id);

        // Prevent self-deletion
        if ($user->id === request()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot permanently delete your own account',
            ], 400);
        }

        $user->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'User permanently deleted',
        ]);
    }

    /**
     * Toggle user active status
     */
    public function toggleActive(string $id)
    {
        $user = User::findOrFail($id);

        // Prevent self-deactivation
        if ($user->id === request()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot deactivate your own account',
            ], 400);
        }

        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'success' => true,
            'message' => $user->is_active ? 'User activated successfully' : 'User deactivated successfully',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, string $id)
    {
        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $user = User::findOrFail($id);
        
        $user->update([
            'password' => Hash::make($request->password),
            'is_first_login' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
        ]);
    }
}
