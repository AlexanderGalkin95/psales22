<?php

namespace App\Models;

use App\Events\BitrixIntegrationInstalled;
use App\Helpers\LoadIntegrationTypes;
use App\Jobs\DistributeIntegrationDataByProject;
use App\Repositories\BitrixRepository;
use App\Repositories\Facades\BitrixRepository as Repository;
use App\Services\Bitrix\BitrixResponse;
use App\Services\Bitrix\Facades\BitrixHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read Integration integration
 */
class BitrixCode extends Model
{
    use HasFactory;

    const CALL_SUCCESS = 200;

    protected $fillable = [
        'client_id',
        'client_secret',
        'domain',
        'is_webhook',
        'webhook_url',
        "scope",
        "server_endpoint",
        "client_endpoint",
        "user_id",
        "member_id",
    ];

    protected $hidden = [
        'client_id',
        'client_secret',
        'member_id',
        'server_endpoint',
        'client_endpoint',
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

    public function bitrixToken(): HasOne
    {
        return $this->hasOne(BitrixToken::class, 'bitrix_code_id');
    }

    public function token(): HasOne
    {
        return $this->bitrixToken();
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function (BitrixCode $model) {
            $model->bitrixToken()->delete();
        });

        static::created(function (BitrixCode $model) {

            event(new BitrixIntegrationInstalled($model));
        });
    }

    public function requestFreshToken(): BitrixResponse
    {
        return BitrixHelper::requestFreshToken($this);
    }

    public function loadCalls($request, $projectId): array
    {
        return Repository::loadCalls($request, $projectId);
    }

    public function reportConnectionError($integrable = null, $error = null)
    {
        $model = $integrable ?? $this;
        $bitrix = new BitrixRepository($model);
        $bitrix->reportConnectionError($error);
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
