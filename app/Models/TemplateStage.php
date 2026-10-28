<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_template_id',
        'name',
        'description',
        'order',
        'gate_requirements',
    ];

    protected $casts = [
        'order' => 'integer',
        'gate_requirements' => 'array',
    ];

    public function workflowTemplate(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplate::class);
    }

    public function templateTasks(): HasMany
    {
        return $this->hasMany(TemplateTask::class)->orderBy('order');
    }
}

