<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subject_id',
        'template_id',
        'title',
        'description',
        'deadline',
        'published_at',
        'is_graded',
        'grading_type',
        'max_score',
        'submission_type',
        'allowed_file_types',
        'max_file_size',
        'max_files',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'published_at' => 'datetime',
            'is_graded' => 'boolean',
            'is_active' => 'boolean',
            'allowed_file_types' => 'array',
            'max_file_size' => 'integer',
            'max_files' => 'integer',
            'max_score' => 'integer',
        ];
    }

    /**
     * Relationships
     */
    
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function template()
    {
        return $this->belongsTo(AssignmentTemplate::class, 'template_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Query Scopes
     */
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->where('published_at', '<=', now());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('deadline', '>', now());
    }

    public function scopeOverdue($query)
    {
        return $query->where('deadline', '<', now());
    }

    public function scopeBySubject($query, int $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Accessors
     */
    
    public function getIsOverdueAttribute(): bool
    {
        return $this->deadline < now();
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->published_at && $this->published_at <= now();
    }
}
