<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'user_notifications';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Relationships
     */
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Types
     */
    
    public const TYPE_ASSIGNMENT_CREATED = 'assignment_created';
    public const TYPE_DEADLINE_REMINDER = 'deadline_reminder';
    public const TYPE_GRADED = 'graded';
    public const TYPE_RESUBMIT_REQUIRED = 'resubmit_required';
    public const TYPE_SYSTEM = 'system';

    public const TYPES = [
        self::TYPE_ASSIGNMENT_CREATED,
        self::TYPE_DEADLINE_REMINDER,
        self::TYPE_GRADED,
        self::TYPE_RESUBMIT_REQUIRED,
        self::TYPE_SYSTEM,
    ];
}
