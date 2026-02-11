<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assignment_id' => $this->assignment_id,
            'student_id' => $this->student_id,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'score' => $this->score,
            'grade' => $this->grade,
            'teacher_comments' => $this->teacher_comments,
            'student_comments' => $this->student_comments,
            'graded_at' => $this->graded_at?->toIso8601String(),
            'graded_by' => $this->graded_by,
            'resubmission_deadline' => $this->resubmission_deadline?->toIso8601String(),
            'resubmission_count' => $this->resubmission_count,
            'is_overdue' => $this->is_overdue,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'is_submitted' => $this->isSubmitted,
            'is_graded' => $this->isGraded,
            'needs_resubmission' => $this->needsResubmission,
            'assignment' => new AssignmentResource($this->whenLoaded('assignment')),
            'student' => new UserResource($this->whenLoaded('student')),
            'grader' => new UserResource($this->whenLoaded('grader')),
        ];
    }
}
