<?php


namespace App\Services\AmoCRM\Helpers;


use App\Models\Project;
use App\Models\AmoCode;
use App\Models\AmoToken;
use App\Models\AmoCallStatus;
use App\Models\IntegrationPipeline;
use App\Models\Call;
use App\Models\SalesManager;
use App\Services\AmoCRM\AmoCRMResponse;
use App\Services\AmoCRM\Facades\AmoCRM;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AmoCRMHelper
{
    public AmoCode $amoCode;

    protected ?AmoCRMResponse $response = null;

    public $scheduleId = null;


    public function requestToken(AmoCode $amoCode): AmoCRMResponse
    {
        $this->amoCode = $amoCode;

        $this->runRequestToken();

        $this->saveToken();

        return $this->response;
    }

    public function requestFreshToken(AmoCode $amoCode): AmoCRMResponse
    {
        $this->amoCode = $amoCode;

        $this->runRequestFreshToken();

        $this->saveFreshToken();

        return $this->response;
    }

    /**
     * Request access token for the very first time
     */
    protected function runRequestToken()
    {
        $this->response = AmoCRM::requestToken($this->amoCode->domain, $this->getPostDataForCreateToken());
    }

    /**
     * Request fresh token using a refresh token
     */
    protected function runRequestFreshToken()
    {
        $this->response = AmoCRM::requestToken($this->amoCode->domain, $this->getPostDataForRefreshToken());
    }

    /**
     *
     */
    protected function saveToken()
    {
        $data = $this->response->toArray()['data'];
        $data['expires_in'] = date('Y-m-d H:i:s', time() + $data['expires_in']);
        $data['amo_code_id'] = $this->amoCode->id;
        AmoToken::create($data);
    }

    protected function saveFreshToken()
    {
        $data = $this->response->toArray()['data'];
        $data['expires_in'] = date('Y-m-d H:i:s', time() + $data['expires_in']);
        $data['amo_code_id'] = $this->amoCode->id;
        $this->amoCode->amoToken->update($data);
    }

    protected function getPostDataForRefreshToken(): array
    {
        return [
            'client_id' => $this->amoCode->client_id,
            'client_secret' => $this->amoCode->client_secret,
            "grant_type" => "refresh_token",
            "refresh_token" => $this->amoCode->amoToken->refresh_token,
            'redirect_uri' => config('services.amoCRM.redirect_uri'),
        ];
    }

    protected function getPostDataForCreateToken(): array
    {
        return [
            'client_id' => $this->amoCode->client_id,
            'client_secret' => $this->amoCode->client_secret,
            "grant_type" => "authorization_code",
            "code" => $this->amoCode->code,
            'redirect_uri' => config('services.amoCRM.redirect_uri'),
        ];
    }

    public function runRequestCalls(AmoCode $amoCode, array $params, int $scheduleId): ?AmoCRMResponse
    {
        $this->amoCode = $amoCode;
        $this->scheduleId = $scheduleId;

        $this->requestCalls($params);


        return $this->response;
    }

    public function runRequestPipelines(AmoCode $amoCode, array $params,  int $scheduleId): ?AmoCRMResponse
    {
        $this->amoCode = $amoCode;
        $this->scheduleId = $scheduleId;

        $this->requestPipelines($params);

        return $this->response;
    }

    public function runRequestSalesManagers(AmoCode $amoCode, array $params, int $scheduleId): ?AmoCRMResponse
    {
        $this->amoCode = $amoCode;
        $this->scheduleId = $scheduleId;

        $this->requestSalesManagers($params);

        return $this->response;
    }

    protected function requestCalls(array $params)
    {
        $headers = [
            'Sec-Fetch-Dest' => 'empty',
            'Sec-Fetch-Site' => 'same-origin',
            'Sec-Fetch-Mode' => 'cors',
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => "Bearer {$this->amoCode->amoToken->access_token}"
        ];
        $this->response = AmoCRM::requestCalls($this->amoCode->domain, $params, $headers);

        $data = (array)$this->response->toArray()['data']['response'];
        if (empty($data)) {
            return true;
        }

        if (empty($data['items'])) {
            return true;
        }

        $this->saveCalls($this->amoCode, $data);

        if (count($data['items']) >= 20) {
            $params['filter']['notes_page']++;
            $this->requestCalls($params);
        }

        return $this->response;
    }

    protected function requestPipelines(array $params)
    {
        $headers = [
            'Authorization' => "Bearer {$this->amoCode->amoToken->access_token}"
        ];

        $this->response = AmoCRM::requestPipelines($this->amoCode->domain, $params, $headers);

        $data = $this->response->toArray()['data'];
        $pipelines = $data['_embedded']['pipelines'];

        if (empty($pipelines)) {
            return true;
        }

        $this->savePipelines($this->amoCode, $pipelines);

        return $this->response;
    }
    protected function requestSalesManagers(array $params)
    {
        $headers = [
            'Authorization' => "Bearer {$this->amoCode->amoToken->access_token}"
        ];
        $this->response = AmoCRM::requestSalesManagers($this->amoCode->domain, $params, $headers);

        $data = $this->response->toArray()['data'];
        $users = (array)$data['_embedded']['users'];
        $pagination = [
            "page" => $data['_page'],
            "page_count" => $data['_page_count'],
            "total_items" => $data['_total_items'],
        ];

        if (empty($users)) {
            return true;
        }

        $this->saveSalesManagers($this->amoCode, $users);

        if ($pagination['page'] < $pagination['page_count']) {
            $params['filter']['page']++;
            $this->requestSalesManagers($params);
        }

        return $this->response;
    }

    /**
     * @param Project $project
     * @param $data
     * @return bool
     */
    public function saveCalls(AmoCode $amoCode, $data): bool
    {
        $insertable = [];

        $amoCallStatuses = Arr::collapse(
            AmoCallStatus::all()
                ->transform(function ($item) {
                    return [$item->name => $item->system_name];
                })
        );

        foreach ($data['items'] as $datum) {
            $createdAt = $datum['date_create'];
            [$date, $time] = explode(' ', $datum['date_create']);
            if (count(explode(':', $time)) === 2) {
                $time = "$time:00";
                $createdAt = "$date $time";
            }
            $status = $datum['result']['status']['text'];
            $insertable[] = [
                'schedule_id' => $this->scheduleId,
                'integration_id' => $amoCode->integration->id,
                'record_id' => $datum['id'],
                'record_created_at' => date('Y-m-d H:i:s', strtotime($createdAt)),
                'record_event_type' => $datum['event']['type'],
                'record_responsible_id' => $datum['event']['main_user_id'],
                'record_responsible_name' => $datum['event']['user_login'],
                'record_element_id' => $datum['event']['element_id'],
                'record_element_name' => $datum['event']['element']['name'] ?? '',
                'record_element_type' => $datum['event']['element']['type'] ?? 1,
                'record_element_link' => $datum['event']['element']['link'] ?? '-',
                'record_status' => $amoCallStatuses[$status] ?? $status,
                'record_duration' => $datum['result']['duration']
                    ? gmdate('H:i:s', $datum['result']['duration'])
                    : null,
                'record_link' => $datum['result']['link'],
                'record_source' => 'AmoCRM',
            ];
        }

        DB::transaction(function () use ($insertable) {
            Call::upsert($insertable, 'record_id');
        });

        return true;
    }

    public function savePipelines(AmoCode $amoCode, $pipelines)
    {
        DB::transaction(function () use ($amoCode, $pipelines) {
            collect($pipelines)->each(function ($pipeline) use ($amoCode) {
                $statuses = $pipeline['_embedded']['statuses'] ?? [];
                $pipeline = IntegrationPipeline::updateOrCreate(
                    ['pipeline_id' => $pipeline['id']],
                    [
                        'schedule_id' => $this->scheduleId,
                        'integration_id' => $amoCode->integration->id,
                        'pipeline_id' => $pipeline['id'],
                        'name' => $pipeline['name'],
                        'sort' => $pipeline['sort'],
                        'is_main' => $pipeline['is_main'],
                        'is_unsorted_on' => $pipeline['is_unsorted_on'],
                        'is_archive' => $pipeline['is_archive'],
                        'account_id' => $pipeline['account_id'],
                        'source' => 'AmoCRM',
                    ]
                );

                foreach ($statuses as $status) {
                    $pipeline->statuses()->updateOrCreate(
                        [
                            'pipeline_id' => $status['pipeline_id'],
                            'status_id' => $status['id']
                        ],
                        [
                            'pipeline_id' => $status['pipeline_id'],
                            'status_id' => $status['id'],
                            'name' => $status['name'],
                            'sort' => $status['sort'],
                            'is_editable' => $status['is_editable'],
                            'color' => $status['color'],
                            'type' => $status['type'],
                            'account_id' => $status['account_id'],
                        ]
                    );
                }
            });
        });

        return true;
    }
    /**
     * @param AmoCode $amoCode
     * @param $data
     * @return bool
     */
    public function saveSalesManagers(AmoCode $amoCode, array $users): bool
    {
        $insertable = [];

        foreach ($users as $datum) {
            $insertable[] = [
                'schedule_id' => $this->scheduleId,
                'foreign_manager_id' => $datum['id'],
                'integration_id' => $amoCode->integration->id,
                'name' => $datum['name'],
                'email' => $datum['email'],
                'is_admin' => $datum['rights']['is_admin'],
                'is_active' => $datum['rights']['is_active'],
                'group_id' => $datum['rights']['group_id'],
                'group_name' => $datum['rights']['group_id'] ? $datum['_embedded']['groups'][0]['name'] : 'Отдел продаж',
                'source' => 'AmoCRM',
            ];
        }

        DB::transaction(function () use ($insertable) {
            SalesManager::upsert($insertable, 'foreign_manager_id');
        });

        return true;
    }
}
