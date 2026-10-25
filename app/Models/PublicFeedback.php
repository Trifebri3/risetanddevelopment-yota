<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicFeedback extends Model
{
    use HasFactory;

    protected $table = 'public_feedbacks';

    protected $fillable = [
        'project_id',
        'reviewer_name',
        'reviewer_role',
        'comment',
        'rating',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
