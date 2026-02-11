<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectResource extends JsonResource
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
            'code' => $this->code,
            'name' => $this->name,
            'academic_year_id' => $this->academic_year_id,
            'term_id' => $this->term_id,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'academic_year' => new AcademicYearResource($this->whenLoaded('academicYear')),
            'term' => new TermResource($this->whenLoaded('term')),
            'teachers' => UserResource::collection($this->whenLoaded('teachers')),
            'students' => UserResource::collection($this->whenLoaded('students')),
            'teachers_count' => $this->when(isset($this->teachers_count), $this->teachers_count),
            'students_count' => $this->when(isset($this->students_count), $this->students_count),
            'assignments_count' => $this->when(isset($this->assignments_count), $this->assignments_count),
        ];
    }
}
