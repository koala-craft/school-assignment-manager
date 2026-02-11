<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\Submission;

class NotificationService
{
    /**
     * Notify enrolled students when an assignment is published
     */
    public static function notifyAssignmentPublished(Assignment $assignment): void
    {
        $assignment->load(['subject.students', 'subject.enrollments']);

        $students = $assignment->subject->students()
            ->wherePivot('is_active', true)
            ->get();

        foreach ($students as $student) {
            $settings = NotificationSetting::firstOrCreate(
                ['user_id' => $student->id],
                NotificationSetting::getDefaults()
            );

            if (!$settings->assignment_created) {
                continue;
            }

            Notification::create([
                'user_id' => $student->id,
                'type' => Notification::TYPE_ASSIGNMENT_CREATED,
                'title' => '新しい課題が登録されました',
                'message' => "「{$assignment->subject->name}」で「{$assignment->title}」が登録されました",
                'data' => [
                    'assignment_id' => $assignment->id,
                    'subject_id' => $assignment->subject_id,
                ],
            ]);
        }
    }

    /**
     * Notify student when submission is graded
     */
    public static function notifySubmissionGraded(Submission $submission): void
    {
        $settings = NotificationSetting::firstOrCreate(
            ['user_id' => $submission->student_id],
            NotificationSetting::getDefaults()
        );

        if (!$settings->graded) {
            return;
        }

        $submission->load('assignment.subject');

        Notification::create([
            'user_id' => $submission->student_id,
            'type' => Notification::TYPE_GRADED,
            'title' => '採点が完了しました',
            'message' => "「{$submission->assignment->subject->name}」の「{$submission->assignment->title}」の採点が完了しました（{$submission->score}点）",
            'data' => [
                'submission_id' => $submission->id,
                'assignment_id' => $submission->assignment_id,
                'subject_id' => $submission->assignment->subject_id,
                'score' => $submission->score,
            ],
        ]);
    }

    /**
     * Notify student when resubmission is required
     */
    public static function notifyResubmissionRequired(Submission $submission): void
    {
        $settings = NotificationSetting::firstOrCreate(
            ['user_id' => $submission->student_id],
            NotificationSetting::getDefaults()
        );

        if (!$settings->resubmit_required) {
            return;
        }

        $submission->load('assignment.subject');

        Notification::create([
            'user_id' => $submission->student_id,
            'type' => Notification::TYPE_RESUBMIT_REQUIRED,
            'title' => '再提出が必要です',
            'message' => "「{$submission->assignment->subject->name}」の「{$submission->assignment->title}」の再提出が必要です",
            'data' => [
                'submission_id' => $submission->id,
                'assignment_id' => $submission->assignment_id,
                'subject_id' => $submission->assignment->subject_id,
            ],
        ]);
    }
}
