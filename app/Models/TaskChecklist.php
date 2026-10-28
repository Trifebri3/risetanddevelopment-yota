<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'item',
        'is_completed',
        'order',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'order' => 'integer',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}

