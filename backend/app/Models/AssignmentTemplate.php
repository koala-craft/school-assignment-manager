<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'name',
        'title',
        'description',
        'grading_type',
        'max_score',
        'submission_type',
        'allowed_file_types',
        'max_file_size',
        'max_files',
    ];

    protected function casts(): array
    {
        return [
            'allowed_file_types' => 'array',
            'max_file_size' => 'integer',
            'max_files' => 'integer',
            'max_score' => 'integer',
        ];
    }

    /**
     * Relationships
     */
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'template_id');
    }
}
