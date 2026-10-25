<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'milestone_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'weight',
    ];

    protected $casts = [
        'due_date' => 'date:Y-m-d',
        'weight' => 'integer',
    ];

    protected static function booted()
    {
        static::saved(function ($task) {
            $task->project?->recalculateProgress();
        });

        static::deleted(function ($task) {
            $task->project?->recalculateProgress();
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }
}
