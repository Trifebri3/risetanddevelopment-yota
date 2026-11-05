<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectPitchDeck extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'tagline',
        'audience',
        'status',
        'theme',
        'presentation_url',
        'file_path',
        'slides',
    ];

    protected $casts = [
        'slides' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
