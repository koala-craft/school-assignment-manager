<?php

use App\Http\Controllers\Api\AcademicYearController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\AssignmentTemplateController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NotificationSettingController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\BackupController;
use App\Http\Controllers\Api\SystemSettingController;
use App\Http\Controllers\Api\SubmissionController;
use App\Http\Controllers\Api\TermController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Auth routes (public)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
    Route::post('/password/reset', [AuthController::class, 'resetPassword']);
    
    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/password/change', [AuthController::class, 'changePassword']);
    });
});

// Admin routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // User management
    Route::apiResource('users', UserController::class);
    Route::post('/users/{user}/restore', [UserController::class, 'restore']);
    Route::delete('/users/{user}/force', [UserController::class, 'forceDestroy']);
    Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive']);
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
    
    // Academic year management
    Route::apiResource('academic-years', AcademicYearController::class);
    Route::post('/academic-years/{academic_year}/set-active', [AcademicYearController::class, 'setActive']);
    Route::get('/academic-years/current/active', [AcademicYearController::class, 'current']);
    
    // Term management
    Route::apiResource('terms', TermController::class);
    
    // Subject management
    Route::apiResource('subjects', SubjectController::class);
    Route::post('/subjects/{subject}/assign-teachers', [SubjectController::class, 'assignTeachers']);
    Route::delete('/subjects/{subject}/teachers/{teacher}', [SubjectController::class, 'removeTeacher']);
    Route::get('/subjects/{subject}/students', [SubjectController::class, 'students']);
    
    // Enrollment management
    Route::apiResource('enrollments', EnrollmentController::class)->only(['index', 'destroy']);
    Route::post('/subjects/{subject}/enroll', [EnrollmentController::class, 'enrollStudents']);
    Route::delete('/subjects/{subject}/students/{student}', [EnrollmentController::class, 'unenrollStudent']);
    Route::patch('/enrollments/{enrollment}/toggle-active', [EnrollmentController::class, 'toggleActive']);
    Route::post('/enrollments/bulk', [EnrollmentController::class, 'bulkEnroll']);
    
    // Assignment template management (Admin)
    // Note: 一般ユーザー向けの assignment-templates ルートと名前が競合しないように、名前空間付きにする
    Route::apiResource('assignment-templates', AssignmentTemplateController::class)
        ->names('admin.assignment-templates');
    
    // Assignment management (Admin)
    Route::apiResource('assignments', AssignmentController::class);
    Route::post('/assignments/{assignment}/publish', [AssignmentController::class, 'publish']);
    Route::post('/assignments/{assignment}/unpublish', [AssignmentController::class, 'unpublish']);
    Route::get('/assignments/{assignment}/statistics', [AssignmentController::class, 'statistics']);
    
    // Submission management (Admin - grading)
    Route::get('/submissions', [SubmissionController::class, 'index']);
    Route::get('/submissions/{submission}', [SubmissionController::class, 'show']);
    Route::post('/submissions/{submission}/grade', [SubmissionController::class, 'grade']);
    Route::delete('/submissions/{submission}', [SubmissionController::class, 'destroy']);
    Route::post('/assignments/{assignment}/submissions/bulk-create', [SubmissionController::class, 'bulkCreate']);
    Route::get('/assignments/{assignment}/submissions/statistics', [SubmissionController::class, 'statisticsByAssignment']);
    
    // System settings (Admin only)
    Route::get('/system-settings', [SystemSettingController::class, 'index']);
    Route::put('/system-settings', [SystemSettingController::class, 'update']);

    // System backup (Admin only)
    Route::post('/system/backup', [BackupController::class, 'store']);
    Route::get('/system/backups', [BackupController::class, 'index']);

    // Audit logs (Admin only)
    Route::get('/audit-logs', [AuditLogController::class, 'index']);
    Route::get('/audit-logs/{id}', [AuditLogController::class, 'show']);
});

// Teacher & Student routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Assignment templates (Teachers can manage their own)
    Route::apiResource('assignment-templates', AssignmentTemplateController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    
    // Subjects for teachers (担当科目のみ)
    Route::get('/teacher/subjects', [SubjectController::class, 'indexForTeacher']);
    
    // Assignments (View and submit)
    Route::get('/assignments', [AssignmentController::class, 'index']);
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show']);
    
    // Submissions (Students submit, Teachers grade)
    Route::post('/assignments/{assignment}/submit', [SubmissionController::class, 'submit']);
    // 一覧取得（教師は担当科目の提出のみ、学生は自分の提出のみ）
    Route::get('/submissions', [SubmissionController::class, 'index']);
    Route::get('/my-submissions', [SubmissionController::class, 'index']);
    // 詳細＋採点
    Route::get('/submissions/{submission}', [SubmissionController::class, 'show']);
    Route::post('/submissions/{submission}/grade', [SubmissionController::class, 'grade']);
    
    // File management
    Route::post('/files/upload', [FileController::class, 'upload']);
    Route::get('/files', [FileController::class, 'index']);
    Route::get('/files/{file}', [FileController::class, 'show']);
    Route::get('/files/{file}/download', [FileController::class, 'download']);
    Route::delete('/files/{file}', [FileController::class, 'destroy']);
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/{notification}', [NotificationController::class, 'show']);
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    
    // Notification settings
    Route::get('/notification-settings', [NotificationSettingController::class, 'index']);
    Route::put('/notification-settings', [NotificationSettingController::class, 'update']);
    
    // Reports (Admin, Teacher for subject, Student for own grades)
    Route::get('/reports/submissions/csv', [ReportController::class, 'submissionsCsv']);
    Route::get('/reports/grades/csv', [ReportController::class, 'gradesCsv']);
    Route::get('/reports/not-submitted/csv', [ReportController::class, 'notSubmittedCsv']);
    Route::get('/reports/student-grades/csv', [ReportController::class, 'studentGradesCsv']);
    Route::get('/reports/student-grades/pdf', [ReportController::class, 'studentGradesPdf']);

    // Dashboard
    Route::get('/dashboard/admin', [DashboardController::class, 'admin']);
    Route::get('/dashboard/teacher', [DashboardController::class, 'teacher']);
    Route::get('/dashboard/student', [DashboardController::class, 'student']);

    // Performance Testing (開発環境のみ)
    if (app()->environment(['local', 'testing'])) {
        Route::get('/performance/test-dashboard', [\App\Http\Controllers\Api\PerformanceTestController::class, 'testDashboard']);
    }
});
