<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // created_atを安全に処理（Carbonインスタンスでない場合はパース）
        $createdAt = $this->created_at;
        if ($createdAt && !($createdAt instanceof \Carbon\Carbon)) {
            $createdAt = Carbon::parse($createdAt);
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->whenLoaded('user', fn () => $this->user?->name),
            'user' => $this->when(
                $this->relationLoaded('user') && $this->user,
                fn () => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ]
            ),
            'action' => $this->action,
            'model' => $this->model,
            'model_id' => $this->model_id,
            'changes' => $this->changes,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'created_at' => $createdAt?->toIso8601String(),
        ];
    }
}
