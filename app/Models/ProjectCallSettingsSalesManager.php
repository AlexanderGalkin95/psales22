<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectCallSettingsSalesManager extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'sales_manager_id',
        'duration_limit',
        'no_duration_limit',
    ];

    protected $visible = [
        'id',
        'project_id',
        'sales_manager_id',
        'duration_limit',
        'no_duration_limit',
        'salesManager',
    ];
    protected $with = [
        'salesManager'
    ];

    /**
     * Get the user that owns the SalesManager
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function salesManager(): BelongsTo
    {
        return $this->belongsTo(SalesManager::class);
    }

    /**
     * Get all of the assessors for the ProjectCallSettingsSalesManager
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assessors(): HasMany
    {
        return $this->hasMany(ProjectManagerAssessors::class, 'project_call_settings_sales_manager_id');
    }
}
