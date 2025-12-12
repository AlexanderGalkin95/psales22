<?php

namespace App\Models;

use App\Repositories\Facades\ProjectRepository;
use App\Traits\ProjectIntegrationTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property integer id
 * @property integer pm_id
 * @property integer senior_id
 * @property integer assessor_id
 * @property integer rating_id
 * @property integer integration_id
 * @property integer project_type
 * @property integer company_id
 *
 * @property string name
 * @property string integration_domain
 * @property string google_connection
 *
 * @property string google_spreadsheet
 * @property string google_spreadsheet_id
 *
 * @property integer total_time_limit
 * @property integer permissible_error
 * @property boolean integration_status
 * @property boolean tasks_generation_status
 *
 * @property Carbon google_last_checked
 * @property Carbon date_start
 *
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property-read User|null pm
 * @property-read User|null senior
 * @property-read User[]|Collection assessors
 * @property-read Integration|null integration
 */
class Project extends Model
{
    use HasFactory;
    use ProjectIntegrationTrait;

    protected $table = 'projects';

//    protected $fillable = [
//        'name',
//        'integration_domain',
//        'google_conection',
//        'google_spreadsheet',
//        'google_last_checked',
//        'rating_id',
//        'integration_status',
//        'pm_id',
//        'senior_id',
//        'integration_id',
//        'project_type',
//        'company_id',
//        'date_start',
//        'total_time_limit',
//        'permissible_error'
//    ];

    protected $guarded = [];

    protected $hidden = ['pivot'];


    /**
     * todo refactoring
     */
    public function getReferenceName(): ?string
    {
        $names = [
            1 => 'AmoCRM',
            2 => 'Bitrix24',
        ];
        return $names[$this->project_type] ?? null;
    }


    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function callRatings(): HasMany
    {
        return $this->hasMany(CallRating::class, 'project_id')
            ->whereNotNull('duration');
    }

    public function call_types(): HasMany
    {
        return $this->hasMany(ProjectCallType::class, 'project_id')->orderBy('id');
    }

    public static function projectForExtension($projectId): ?object
    {
        $project = ProjectRepository::findById($projectId);

        if (empty($project)) {
            return null;
        }

        $integrable = resolve($project->reference->type)
            ->with('token')
            ->where('domain', '=', $project->integration_domain)
            ->first();

        $response = [
            'id' => $project->id,
            'name' => $project->name,
            'domain' => $project->integration_domain,
            'access_token' => $integrable ? $integrable->token->access_token : null,
        ];

        return (object)$response;
    }

    public function pm(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pm_id', '');
    }

    public function assessors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, ProjectAssessor::class);
    }

    public function senior(): BelongsTo
    {
        return $this->belongsTo(User::class, 'senior_id');
    }

    public function rating(): BelongsTo
    {
        return $this->belongsTo(Rating::class, 'rating_id');
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(Criteria::class, 'project_id')
            ->orderBy('criteria.index_number');
    }

    public function additionalCriteria(): HasMany
    {
        return $this->hasMany(AdditionalCriteria::class, 'project_id')
            ->with('options')
            ->orderBy('additional_criteria.index_number');
    }

    public function crm(): HasMany
    {
        return $this->hasMany(ProjectCrmField::class)->orderBy('index_number');
    }

    public function objections(): HasMany
    {
        return $this->hasMany(ProjectObjectionField::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(ProjectSettings::class);
    }

    public function calls(): BelongsToMany
    {
        return $this->belongsToMany(Call::class, 'project_calls', 'project_id', 'call_id')
            ->orderByRaw('calls.record_duration DESC NULLS LAST')
            ->withPivot('id');
    }

    public function callSettings(): HasOne
    {
        return $this->hasOne(ProjectCallSetting::class, 'project_id');
    }

    public function callSettingsSalesManagers(): HasMany
    {
        return $this->hasMany(ProjectCallSettingsSalesManager::class);
    }

    public function callSettingsIntegrationPipelines(): HasMany
    {
        return $this->hasMany(ProjectCallSettingsIntegrationPipeline::class);
    }

    public function toggleTasksGenerationStatus()
    {
        $this->tasks_generation_status = !$this->tasks_generation_status;

        return $this->save();
    }
}
