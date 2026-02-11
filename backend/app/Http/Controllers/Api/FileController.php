<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubmissionFileResource;
use App\Models\Submission;
use App\Models\SubmissionFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * Get files for a submission
     */
    public function index(Request $request)
    {
        $query = SubmissionFile::query();

        if ($request->has('submission_id')) {
            $query->where('submission_id', $request->submission_id);
        }

        if ($request->boolean('with_submission')) {
            $query->with('submission');
        }

        $sortBy = $request->input('sort_by', 'uploaded_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $files = $query->paginate($perPage);

        return SubmissionFileResource::collection($files);
    }

    /**
     * Upload a file
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:102400'], // 100MB
            'submission_id' => ['required', 'exists:submissions,id'],
        ]);

        $submission = Submission::findOrFail($request->submission_id);

        // Check if user owns this submission or is admin/teacher
        $user = $request->user();
        if ($submission->student_id !== $user->id && !in_array($user->role, ['admin', 'teacher'])) {
            return response()->json([
                'success' => false,
                'message' => 'このファイルをアップロードする権限がありません',
            ], 403);
        }

        $file = $request->file('file');
        $originalFilename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = Str::random(40) . '.' . $extension;
        $storagePath = 'submissions/' . $submission->assignment_id . '/' . $submission->student_id . '/' . $filename;

        // Store file
        $file->storeAs('public/submissions/' . $submission->assignment_id . '/' . $submission->student_id, $filename);

        // Get latest version
        $latestVersion = SubmissionFile::where('submission_id', $submission->id)->max('version') ?? 0;

        // Create file record
        $submissionFile = SubmissionFile::create([
            'submission_id' => $submission->id,
            'filename' => $filename,
            'original_filename' => $originalFilename,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'storage_path' => $storagePath,
            'version' => $latestVersion + 1,
            'uploaded_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'ファイルをアップロードしました',
            'data' => new SubmissionFileResource($submissionFile),
        ], 201);
    }

    /**
     * Download a file
     */
    public function download(string $id)
    {
        $file = SubmissionFile::with('submission')->findOrFail($id);

        // Check permission
        $user = request()->user();
        $submission = $file->submission;
        
        if ($submission->student_id !== $user->id && !in_array($user->role, ['admin', 'teacher'])) {
            return response()->json([
                'success' => false,
                'message' => 'このファイルをダウンロードする権限がありません',
            ], 403);
        }

        $filePath = 'public/submissions/' . $submission->assignment_id . '/' . $submission->student_id . '/' . $file->filename;

        if (!Storage::exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'ファイルが見つかりません',
            ], 404);
        }

        return Storage::download($filePath, $file->original_filename);
    }

    /**
     * Delete a file
     */
    public function destroy(string $id)
    {
        $file = SubmissionFile::with('submission')->findOrFail($id);

        // Check permission
        $user = request()->user();
        $submission = $file->submission;
        
        if ($submission->student_id !== $user->id && !in_array($user->role, ['admin', 'teacher'])) {
            return response()->json([
                'success' => false,
                'message' => 'このファイルを削除する権限がありません',
            ], 403);
        }

        // Soft delete
        $file->delete();

        return response()->json([
            'success' => true,
            'message' => 'ファイルを削除しました',
        ]);
    }

    /**
     * Get file info
     */
    public function show(string $id)
    {
        $file = SubmissionFile::with('submission')->findOrFail($id);

        // Check permission
        $user = request()->user();
        $submission = $file->submission;
        
        if ($submission->student_id !== $user->id && !in_array($user->role, ['admin', 'teacher'])) {
            return response()->json([
                'success' => false,
                'message' => 'このファイル情報を閲覧する権限がありません',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new SubmissionFileResource($file),
        ]);
    }
}
