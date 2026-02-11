<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // パフォーマンス測定（開発環境のみ）
        $startTime = microtime(true);
        
        $user = User::where('email', $request->email)->first();
        $dbTime = microtime(true) - $startTime;

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // パスワード検証（これが最も重い処理）
        $hashStartTime = microtime(true);
        $passwordValid = Hash::check($request->password, $user->password);
        $hashTime = microtime(true) - $hashStartTime;

        if (!$passwordValid) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['This account has been deactivated.'],
            ]);
        }

        // Create token
        $tokenStartTime = microtime(true);
        $token = $user->createToken('auth-token')->plainTextToken;
        $tokenTime = microtime(true) - $tokenStartTime;

        // 開発環境でのパフォーマンスログ
        if (app()->environment(['local', 'testing'])) {
            Log::info('Login Performance', [
                'db_query_ms' => round($dbTime * 1000, 2),
                'hash_check_ms' => round($hashTime * 1000, 2),
                'token_create_ms' => round($tokenTime * 1000, 2),
                'total_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);
        }

        // 監査ログはレスポンス送信後に記録（ログイン体感速度の短縮）
        $userId = $user->id;
        app()->terminating(function () use ($userId): void {
            AuditLogService::logLogin($userId);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'student_number' => $user->student_number,
                    'is_first_login' => $user->is_first_login,
                ],
                'token' => $token,
            ],
            'message' => 'Login successful',
        ]);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        AuditLogService::logLogout($user->id);
        $user->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get current user information
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'student_number' => $user->student_number,
                'is_first_login' => $user->is_first_login,
                'created_at' => $user->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Update current user profile (name, email)
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $request->user()->id],
        ]);

        $user = $request->user();
        $user->update($request->only('name', 'email'));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'student_number' => $user->student_number,
                'is_first_login' => $user->is_first_login,
                'created_at' => $user->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'is_first_login' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    }

    /**
     * パスワードリセット要求
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'パスワードリセットのメールを送信しました',
        ]);
    }

    /**
     * パスワードリセット実行
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'is_first_login' => false,
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'パスワードをリセットしました',
        ]);
    }
}
