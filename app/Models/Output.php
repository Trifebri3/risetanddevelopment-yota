<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Output extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'output_type',
        'status',
        'target_date',
        'venue_or_journal',
        'doi_or_url',
        'evidence_link',
        'is_public',
        'notes',
    ];

    protected $casts = [
        'target_date' => 'date:Y-m-d',
        'is_public' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
