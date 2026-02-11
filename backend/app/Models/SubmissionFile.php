<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubmissionFile extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'submission_id',
        'filename',
        'original_filename',
        'file_size',
        'mime_type',
        'storage_path',
        'version',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
            'file_size' => 'integer',
            'version' => 'integer',
        ];
    }

    /**
     * Relationships
     */
    
    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * Accessors
     */
    
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->storage_path);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
