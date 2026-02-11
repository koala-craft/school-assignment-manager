<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionFileResource extends JsonResource
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
            'submission_id' => $this->submission_id,
            'filename' => $this->filename,
            'original_filename' => $this->original_filename,
            'file_size' => $this->file_size,
            'file_size_formatted' => $this->file_size_formatted,
            'mime_type' => $this->mime_type,
            'storage_path' => $this->storage_path,
            'url' => $this->url,
            'version' => $this->version,
            'uploaded_at' => $this->uploaded_at->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'submission' => new SubmissionResource($this->whenLoaded('submission')),
        ];
    }
}
