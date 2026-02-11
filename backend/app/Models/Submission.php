<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'status',
        'score',
        'grade',
        'teacher_comments',
        'student_comments',
        'submitted_at',
        'graded_at',
        'graded_by',
        'resubmission_deadline',
        'resubmission_count',
        'is_overdue',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'graded_at' => 'datetime',
            'resubmission_deadline' => 'datetime',
            'is_overdue' => 'boolean',
            'score' => 'integer',
            'resubmission_count' => 'integer',
        ];
    }

    /**
     * Relationships
     */
    
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function grader()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Query Scopes
     */
    
    public function scopeByAssignment($query, int $assignmentId)
    {
        return $query->where('assignment_id', $assignmentId);
    }

    public function scopeByStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeNotSubmitted($query)
    {
        return $query->where('status', 'not_submitted');
    }

    public function scopeSubmitted($query)
    {
        return $query->whereIn('status', ['submitted', 'graded', 'resubmit_required', 'resubmitted']);
    }

    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    public function scopeNeedsGrading($query)
    {
        return $query->whereIn('status', ['submitted', 'resubmitted']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_overdue', true);
    }

    /**
     * Accessors
     */
    
    public function getIsSubmittedAttribute(): bool
    {
        return in_array($this->status, ['submitted', 'graded', 'resubmission_requested', 'resubmitted']);
    }

    public function getIsGradedAttribute(): bool
    {
        return $this->graded_at !== null;
    }

    public function getNeedsResubmissionAttribute(): bool
    {
        return $this->status === 'resubmission_requested';
    }
    
    /**
     * Helper Methods
     */
    
    public function isSubmitted(): bool
    {
        return $this->is_submitted;
    }

    public function isGraded(): bool
    {
        return $this->is_graded;
    }

    public function needsResubmission(): bool
    {
        return $this->needs_resubmission;
    }

    public function canTransitionTo(string $newStatus): bool
    {
        $allowedTransitions = [
            'not_submitted' => ['submitted'],
            'submitted' => ['graded', 'resubmit_required'],
            'resubmit_required' => ['resubmitted'],
            'resubmitted' => ['graded', 'resubmit_required'],
            'graded' => ['resubmit_required'],
        ];

        return in_array($newStatus, $allowedTransitions[$this->status] ?? []);
    }
}
