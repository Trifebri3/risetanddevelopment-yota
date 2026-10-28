<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'category',
        'framework',
        'description',
        'icon',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function stages(): HasMany
    {
        return $this->hasMany(TemplateStage::class)->orderBy('order');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}

