<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'phase_id',
        'task_id',
        'date',
        'title',
        'activity',
        'results',
        'challenges',
        'next_steps',
        'entry_type',
        'status',
        'is_public',
        'attachments',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'is_public' => 'boolean',
        'attachments' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(Phase::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
