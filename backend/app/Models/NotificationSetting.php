<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_enabled',
        'assignment_created',
        'deadline_reminder',
        'graded',
        'resubmit_required',
    ];

    protected function casts(): array
    {
        return [
            'email_enabled' => 'boolean',
            'assignment_created' => 'boolean',
            'deadline_reminder' => 'boolean',
            'graded' => 'boolean',
            'resubmit_required' => 'boolean',
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
     * Get default settings
     */
    public static function getDefaults(): array
    {
        return [
            'email_enabled' => true,
            'assignment_created' => true,
            'deadline_reminder' => true,
            'graded' => true,
            'resubmit_required' => true,
        ];
    }
}
