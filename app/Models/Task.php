<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'milestone_id',
        'assigned_to',
        'title',
        'description',
        'instructions',
        'output_deliverable',
        'status',
        'priority',
        'start_date',
        'due_date',
        'weight',
        'stage_gate_status',
        'extension_fields',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'due_date' => 'date:Y-m-d',
        'weight' => 'integer',
        'extension_fields' => 'array',
    ];

    protected $appends = [
        'checklist_progress',
    ];

    protected static function booted(): void
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

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(TaskChecklist::class)->orderBy('order')->orderBy('id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class)->orderByDesc('created_at');
    }

    public function referenceAttachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class)
            ->where('attachment_type', 'reference')
            ->orderBy('title');
    }

    public function deliverableAttachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class)
            ->where('attachment_type', 'deliverable')
            ->orderByDesc('created_at');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(TaskReview::class)->with('user')->orderByDesc('created_at');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->with('user')->orderBy('created_at');
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(TaskDependency::class);
    }

    public function prerequisiteTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_task_id')
            ->withTimestamps();
    }

    public function getChecklistProgressAttribute(): array
    {
        $total = $this->checklists()->count();
        $completed = $total > 0 ? $this->checklists()->where('is_completed', true)->count() : 0;
        $percent = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'percent' => $percent,
        ];
    }
}
