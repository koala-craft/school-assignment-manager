<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
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
            'subject_id' => $this->subject_id,
            'template_id' => $this->template_id,
            'title' => $this->title,
            'description' => $this->description,
            'deadline' => $this->deadline->toIso8601String(),
            'published_at' => $this->published_at?->toIso8601String(),
            'is_graded' => $this->is_graded,
            'grading_type' => $this->grading_type,
            'max_score' => $this->max_score,
            'submission_type' => $this->submission_type,
            'allowed_file_types' => $this->allowed_file_types,
            'max_file_size' => $this->max_file_size,
            'max_files' => $this->max_files,
            'is_active' => $this->is_active,
            'is_overdue' => $this->isOverdue,
            'is_published' => $this->isPublished,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'subject' => new SubjectResource($this->whenLoaded('subject')),
            'template' => new AssignmentTemplateResource($this->whenLoaded('template')),
            'submissions_count' => $this->when(isset($this->submissions_count), $this->submissions_count),
        ];
    }
}
