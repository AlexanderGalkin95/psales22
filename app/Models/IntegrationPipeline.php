<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IntegrationPipeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'integration_id',
        'pipeline_id',
        'name',
        'sort',
        'is_main',
        'is_unsorted_on',
        'is_archive',
        'account_id',
        'source',
    ];

    protected $visible = [
        'id',
        'integration_id',
        'pipeline_id',
        'source_id',
        'name',
        'sort',
        'is_main',
        'is_unsorted_on',
        'is_archive',
        'account_id',
        'source',
        'statuses',
    ];

    protected $with = [
        'statuses'
    ];

    /**
     * Get all of the statuses for the IntegrationPipeline
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function statuses(): HasMany
    {
        return $this->hasMany(IntegrationPipelineStatus::class);
    }
}
