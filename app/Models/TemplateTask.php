<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_stage_id',
        'title',
        'description',
        'instructions',
        'output_deliverable',
        'priority',
        'weight',
        'order',
        'checklists',
        'reference_attachments',
        'extension_fields',
    ];

    protected $casts = [
        'weight' => 'integer',
        'order' => 'integer',
        'checklists' => 'array',
        'reference_attachments' => 'array',
        'extension_fields' => 'array',
    ];

    public function templateStage(): BelongsTo
    {
        return $this->belongsTo(TemplateStage::class);
    }
}

