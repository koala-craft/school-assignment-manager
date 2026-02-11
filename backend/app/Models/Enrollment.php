<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'student_id',
        'enrolled_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relationships
     */
    
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Query Scopes
     */
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySubject($query, int $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeByStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }
}
