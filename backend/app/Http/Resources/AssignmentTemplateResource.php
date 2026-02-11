<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentTemplateResource extends JsonResource
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
            'created_by' => $this->created_by,
            'name' => $this->name,
            'title' => $this->title,
            'description' => $this->description,
            'grading_type' => $this->grading_type,
            'max_score' => $this->max_score,
            'submission_type' => $this->submission_type,
            'allowed_file_types' => $this->allowed_file_types,
            'max_file_size' => $this->max_file_size,
            'max_files' => $this->max_files,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'assignments_count' => $this->when(isset($this->assignments_count), $this->assignments_count),
        ];
    }
}
