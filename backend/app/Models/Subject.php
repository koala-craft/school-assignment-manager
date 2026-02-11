<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'academic_year_id',
        'term_id',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relationships
     */
    
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'subject_teachers', 'subject_id', 'teacher_id')
            ->withTimestamps();
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments', 'subject_id', 'student_id')
            ->withPivot('enrolled_at', 'is_active')
            ->withTimestamps();
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Query Scopes
     */
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByAcademicYear($query, int $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeByTerm($query, int $termId)
    {
        return $query->where('term_id', $termId);
    }

    public function scopeTaughtBy($query, int $teacherId)
    {
        return $query->whereHas('teachers', function ($q) use ($teacherId) {
            $q->where('users.id', $teacherId);
        });
    }
}
