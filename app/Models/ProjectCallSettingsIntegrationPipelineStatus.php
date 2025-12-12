<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectCallSettingsIntegrationPipelineStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_call_settings_integration_pipeline_id',
        'integration_pipeline_status_id',
    ];

    protected $visible = [
        'id',
        'project_call_settings_integration_pipeline_id',
        'integration_pipeline_status_id',
        'status',
    ];
    protected $with = [
        'status'
    ];

    /**
     * Get the user that owns the IntegrationPipeline
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(IntegrationPipelineStatus::class, 'integration_pipeline_status_id');
    }
}
