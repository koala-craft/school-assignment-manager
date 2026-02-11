<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'model',
        'model_id',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'changes' => 'array',
        'model_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 更新・削除を禁止
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        return false;
    }

    public function delete(): ?bool
    {
        return false;
    }
}
