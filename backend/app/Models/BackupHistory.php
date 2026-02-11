<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupHistory extends Model
{
    protected $table = 'backup_history';

    public $timestamps = false;

    protected $fillable = [
        'filename',
        'size',
        'created_at',
    ];

    protected $casts = [
        'size' => 'integer',
        'created_at' => 'datetime',
    ];
}
