<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use Illuminate\Http\Request;

class NotificationSettingController extends Controller
{
    /**
     * Get notification settings for the current user
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $settings = NotificationSetting::firstOrCreate(
            ['user_id' => $userId],
            NotificationSetting::getDefaults()
        );

        return response()->json([
            'success' => true,
            'data' => [
                'email_enabled' => $settings->email_enabled,
                'assignment_created' => $settings->assignment_created,
                'deadline_reminder' => $settings->deadline_reminder,
                'graded' => $settings->graded,
                'resubmit_required' => $settings->resubmit_required,
            ],
        ]);
    }

    /**
     * Update notification settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'email_enabled' => ['boolean'],
            'assignment_created' => ['boolean'],
            'deadline_reminder' => ['boolean'],
            'graded' => ['boolean'],
            'resubmit_required' => ['boolean'],
        ]);

        $userId = $request->user()->id;

        $settings = NotificationSetting::updateOrCreate(
            ['user_id' => $userId],
            array_merge(
                NotificationSetting::getDefaults(),
                $request->only([
                    'email_enabled',
                    'assignment_created',
                    'deadline_reminder',
                    'graded',
                    'resubmit_required',
                ])
            )
        );

        return response()->json([
            'success' => true,
            'message' => '通知設定を更新しました',
            'data' => [
                'email_enabled' => $settings->email_enabled,
                'assignment_created' => $settings->assignment_created,
                'deadline_reminder' => $settings->deadline_reminder,
                'graded' => $settings->graded,
                'resubmit_required' => $settings->resubmit_required,
            ],
        ]);
    }
}
