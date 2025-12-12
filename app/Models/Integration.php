<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property integer id
 * @property integer integration_id
 * @property integer ref_integration_id
 * @property string min_date_start
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @property-read Project[]|Collection projects
 */
class Integration extends Model
{
    use HasFactory;

    protected $table = 'integrations';

    protected $fillable = [
        'integration_id',
        'ref_integration_id',
    ];

    public function reference(): BelongsTo
    {
        return $this->belongsTo(RefIntegration::class, 'ref_integration_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'integration_id');
    }

    /**
     * Get all of the comments for the Integration
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pipelines(): HasMany
    {
        return $this->hasMany(IntegrationPipeline::class);
    }

    public function salesManagers(): HasMany
    {
        return $this->hasMany(SalesManager::class);
    }

    public function getSalesManagers()
    {
        return $this->salesManagers
            ->groupBy('group_name')
            ->map(function ($item, $key) {
                return [
                    'group' => $key,
                    'items' => collect($item)->map(
                        function ($manager) {
                            $manager->no_duration_limit = false;
                            $manager->duration_limit = null;
                            return $manager;
                        }
                    )
                ];
            })
            ->values();
    }

    public function projectsMinDateStart()
    {
        return $this->projects()
            ->selectRaw('MIN(date_start::timestamp::date) as min_date_start')
            ->value('min_date_start');
    }
}
