<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectProposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'target_funder',
        'submission_deadline',
        'status',
        'executive_summary',
        'problem_statement',
        'solution_statement',
        'novelty_and_innovation',
        'methodology',
        'market_and_impact',
        'budget_summary',
        'document_url',
        'file_path',
        'custom_sections',
    ];

    protected $casts = [
        'submission_deadline' => 'date:Y-m-d',
        'custom_sections' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
