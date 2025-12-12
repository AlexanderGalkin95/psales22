<?php

namespace App\Models;

use App\Events\AmoCRMWidgetInstalled;
use App\Helpers\LoadIntegrationTypes;
use App\Jobs\DistributeIntegrationDataByProject;
use App\Repositories\AmoRepository;
use App\Repositories\Facades\AmoRepository as Repository;
use App\Services\AmoCRM\AmoCRMResponse;
use App\Services\AmoCRM\Facades\AmoCRMHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read Integration integration
 */
class AmoCode extends Model
{
    use HasFactory;

    protected $table = 'amo_codes';

    protected $primaryKey = 'id';

    protected $fillable = [
        'client_id',
        'client_secret',
        'domain',
        'test',
        'widget',
        'code',
    ];

    const CALL_IN = 10;
    const CALL_OUT = 11;

    protected $hidden = [
        'client_id',
        'client_secret',
        'code',
    ];

    public function integration(): HasOne
    {
        $refId = LoadIntegrationTypes::getInstance()
            ->getTypes()
            ->firstWhere('type', '=', static::class)
            ->id;
        return $this->hasOne(Integration::class, 'integration_id')
            ->where('integrations.ref_integration_id', '=', $refId);
    }

    public function amoToken(): HasOne
    {
        return $this->hasOne(AmoToken::class, 'amo_code_id');
    }

    public function token(): HasOne
    {
        return $this->amoToken();
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class, 'amo_code_id');
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function (AmoCode $model) {
            $model->amoToken()->delete();
        });

        static::created(function (AmoCode $model) {
            $repository = new AmoRepository($model);
            $repository->saveIntegration();
        });

        static::updated(function (AmoCode $model) {
            $repository = new AmoRepository($model);
            $repository->saveIntegration();
            event(new AmoCRMWidgetInstalled($model));
        });
    }

    public function requestFreshToken(): AmoCRMResponse
    {
        return AmoCRMHelper::requestFreshToken($this);
    }

    public function loadCalls($request, $projectId): array
    {
        return Repository::loadCalls($request, $projectId);
    }

    public function reportConnectionError($integrable = null, $error = null)
    {
        $model = $integrable ?? $this;
        $repo = new AmoRepository($model);
        $repo->reportConnectionError($error);
    }

    public function refreshIntegrationPipelines()
    {
        $integrationSchedule = IntegrationSchedule::create([
            'type' => IntegrationSchedule::SCHEDULE_TYPE_PIPELINES
        ]);

        Repository::refreshIntegrationPipelines($this, $integrationSchedule->id);

        DistributeIntegrationDataByProject::dispatch($integrationSchedule);
    }

    public function refreshSalesManagers()
    {
        $integrationSchedule = IntegrationSchedule::create([
            'type' => IntegrationSchedule::SCHEDULE_TYPE_MANAGERS
        ]);

        Repository::refreshSalesManagers($this, $integrationSchedule->id);

        DistributeIntegrationDataByProject::dispatch($integrationSchedule);
    }
}
