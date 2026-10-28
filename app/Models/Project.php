<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'mode',
        'workflow_template_id',
        'problem_statement',
        'target_outcome',
        'status',
        'start_date',
        'end_date',
        'progress_percent',
        'task_progress_percent',
        'milestone_progress_percent',
        'outcome_progress_percent',
        'share_token',
        'team_invite_token',
        'is_public',
        'public_settings',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'public_settings' => 'array',
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'progress_percent' => 'integer',
        'task_progress_percent' => 'integer',
        'milestone_progress_percent' => 'integer',
        'outcome_progress_percent' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (empty($project->slug)) {
                $base = Str::slug($project->title);
                $base = $base ? Str::limit($base, 90, '') : 'proyek';

                $slug = $base;
                if (static::where('slug', $slug)->exists()) {
                    $slug = $base.'-'.Str::lower(Str::random(6));
                }
                $project->slug = $slug;
            }
            if (empty($project->share_token)) {
                $project->share_token = Str::random(32);
            }
            if (empty($project->team_invite_token)) {
                $project->team_invite_token = Str::random(32);
            }
            if (empty($project->public_settings)) {
                $project->public_settings = [
                    'show_overview' => true,
                    'show_milestones' => true,
                    'show_journal' => true,
                    'show_outputs' => true,
                    'show_budget_summary' => true,
                    'allow_feedback' => true,
                ];
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        $field = $field ?? $this->getRouteKeyName();

        return $query->where(function ($q) use ($field, $value) {
            $q->where($field, $value);
            if (is_numeric($value)) {
                $q->orWhere('id', (int) $value);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ideas(): HasMany
    {
        return $this->hasMany(Idea::class);
    }

    public function phases(): HasMany
    {
        return $this->hasMany(Phase::class)->orderBy('order');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class)->orderBy('due_date');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class)->orderByDesc('date')->orderByDesc('id');
    }

    public function budgetItems(): HasMany
    {
        return $this->hasMany(BudgetItem::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class)->orderByDesc('date');
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(Output::class)->orderBy('target_date');
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(PublicFeedback::class)->orderByDesc('created_at');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class)->orderBy('role');
    }

    public function isOwner(?User $user): bool
    {
        return $user && (int) $this->user_id === (int) $user->id;
    }

    public function hasAccess(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->isOwner($user)) {
            return true;
        }

        return $this->members()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('email', $user->email);
            })
            ->exists();
    }

    public function workflowTemplate(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplate::class);
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(ProjectMetric::class)->orderBy('id');
    }

    public function recalculateProgress(): void
    {
        // 1. Task progress (weighted)
        $totalWeight = (int) $this->tasks()->sum('weight');
        if ($totalWeight > 0) {
            $doneWeight = (int) $this->tasks()->where('status', 'done')->sum('weight');
            $this->task_progress_percent = (int) round(($doneWeight / $totalWeight) * 100);
        } else {
            $this->task_progress_percent = 0;
        }

        // 2. Milestone progress (verified completion)
        $totalMilestones = $this->milestones()->count();
        if ($totalMilestones > 0) {
            $doneMilestones = $this->milestones()->where('status', 'completed')->count();
            $this->milestone_progress_percent = (int) round(($doneMilestones / $totalMilestones) * 100);
        } else {
            $this->milestone_progress_percent = 0;
        }

        // 3. Outcome progress (metrics achieved)
        $totalMetrics = $this->metrics()->count();
        if ($totalMetrics > 0) {
            $achievedMetrics = $this->metrics()->where('status', 'achieved')->count();
            $this->outcome_progress_percent = (int) round(($achievedMetrics / $totalMetrics) * 100);
        } else {
            $this->outcome_progress_percent = 0;
        }

        // Composite overall progress (defaults to task progress if present, or milestone progress)
        $this->progress_percent = $totalWeight > 0 ? $this->task_progress_percent : $this->milestone_progress_percent;

        $this->saveQuietly();
    }
}
