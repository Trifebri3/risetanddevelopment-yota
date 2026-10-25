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
        'problem_statement',
        'target_outcome',
        'status',
        'start_date',
        'end_date',
        'progress_percent',
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
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->title).'-'.Str::lower(Str::random(6));
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

    public function recalculateProgress(): void
    {
        $totalWeight = $this->tasks()->sum('weight');
        if ($totalWeight > 0) {
            $doneWeight = $this->tasks()->where('status', 'done')->sum('weight');
            $this->progress_percent = (int) round(($doneWeight / $totalWeight) * 100);
        } else {
            $totalMilestones = $this->milestones()->count();
            if ($totalMilestones > 0) {
                $doneMilestones = $this->milestones()->where('status', 'completed')->count();
                $this->progress_percent = (int) round(($doneMilestones / $totalMilestones) * 100);
            } else {
                $this->progress_percent = 0;
            }
        }
        $this->saveQuietly();
    }
}
