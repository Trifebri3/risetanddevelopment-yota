<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'category',
        'item_name',
        'qty',
        'unit',
        'estimated_unit_price',
        'total_estimated_price',
        'procurement_status',
    ];

    protected $casts = [
        'qty' => 'integer',
        'estimated_unit_price' => 'decimal:2',
        'total_estimated_price' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::saving(function ($item) {
            $item->total_estimated_price = $item->qty * $item->estimated_unit_price;
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
