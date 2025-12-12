<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectCallSettingsIntegrationPipeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'integration_pipeline_id',
    ];

    protected $visible = [
        'id',
        'project_id',
        'integration_pipeline_id',
        'pipeline',
        'selectedStatuses',
    ];
    protected $with = [
        'pipeline',
        'selectedStatuses'
    ];

    /**
     * Get the user that owns the IntegrationPipeline
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(IntegrationPipeline::class, 'integration_pipeline_id');
    }

    /**
     * Get all of the selectedStatuses for the ProjectCallSettingsIntegrationPipeline
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function selectedStatuses(): HasMany
    {
        return $this->hasMany(
            ProjectCallSettingsIntegrationPipelineStatus::class,
            'project_call_settings_integration_pipeline_id',
        );
    }
}
