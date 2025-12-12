<?php


namespace App\Services\Bitrix\Helpers;


use App\Models\BitrixCode;
use App\Models\BitrixPendingInstall;
use App\Models\Call;
use App\Models\ProjectCall;
use App\Models\SalesManager;
use App\Repositories\BitrixRepository;
use App\Services\Bitrix\BitrixResponse;
use App\Services\Bitrix\Facades\Bitrix;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class BitrixHelper
{
    public BitrixCode $bitrixCode;

    protected ?BitrixResponse $response = null;

    public $scheduleId = null;


    public function request(BitrixCode $bitrixCode, array $params): ?BitrixResponse
    {
        $this->bitrixCode = $bitrixCode;
        $this->response = Bitrix::call(array_merge_recursive($this->getDefaultParams(), $params));

        return $this->response;
    }

    public function batchRequest(BitrixCode $bitrixCode, array $params): ?BitrixResponse
    {
        $this->bitrixCode = $bitrixCode;
        $this->response = Bitrix::callBatch(array_merge_recursive($this->getDefaultParams(), $params));

        return $this->response;
    }

    public function requestFreshToken(BitrixCode $bitrixCode): BitrixResponse
    {
        $this->bitrixCode = $bitrixCode;
        $this->runRequestFreshToken();
        $this->saveFreshToken();

        return $this->response;
    }

    public function runRequestCalls(BitrixCode $bitrixCode, array $params, ?int $scheduleId): array
    {
        $this->bitrixCode = $bitrixCode;
        $this->scheduleId = $scheduleId;

        return $this->requestCalls($params);
    }

    /**
     * Request fresh token using a refresh token
     */
    protected function runRequestFreshToken()
    {
        $this->response = Bitrix::call($this->getPostDataForRefreshToken());
    }

    public function runRequestSalesManagers(BitrixCode $bitrixCode, array $params, int $scheduleId): ?BitrixResponse
    {
        $this->bitrixCode = $bitrixCode;
        $this->scheduleId = $scheduleId;

        $this->requestSalesManagers($params);

        return $this->response;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function savePendingToken(Request $request): bool
    {
        $data = $request->input('auth');
        $data['expires_in'] = date('Y-m-d H:i:s', time() + $data['expires_in']);
        if ($request->query->count()) {
            $mapping = [
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
            ];
            $codeData = [];
            foreach ($request->query->all() as $key => $value) {
                if (Arr::exists($mapping, $key)) {
                    $codeData[$mapping[$key]] = $value;
                }
            }
            if (count($codeData)) {
                $data = array_merge($data, $codeData);
                $code = BitrixCode::where('domain', '=', $data['domain'])
                    ->first() ?? new BitrixCode();
                $bitrix = new BitrixRepository($code);
                $bitrix->fillAndSave($data);
                $bitrix->saveIntegration();
                $bitrix->saveToken($data);
            }
        }
        $pendingInstall = BitrixPendingInstall::where('domain', '=', $data['domain'])
            ->first() ?? new BitrixPendingInstall();
        return $pendingInstall->fill($data)->save();
    }

    /**
     *
     */
    public function saveFreshToken()
    {
        $data = $this->response->toArray()['data'];
        $data['expires_in'] = date('Y-m-d H:i:s', time() + $data['expires_in']);
        $this->bitrixCode->bitrixToken->fill($data)->save();
    }

    protected function getPostDataForRefreshToken(): array
    {
        return [
            'this_auth' => true,
            'query' => [
                'client_id' => $this->bitrixCode->client_id,
                'grant_type' => 'refresh_token',
                'client_secret' => $this->bitrixCode->client_secret,
                'refresh_token' => $this->bitrixCode->bitrixToken->refresh_token,
            ]
        ];
    }

    protected function getDefaultParams(): array
    {
        $params = [
            'this_auth' => false,
            'query' => [
                'client_id' => $this->bitrixCode->client_id,
                'client_secret' => $this->bitrixCode->client_secret,
            ]
        ];

        if (!$this->bitrixCode->is_web_hook) {
            $params['query']['auth'] = $this->bitrixCode->bitrixToken->access_token;
        }
        return $params;
    }

    private function requestCalls($param): array
    {
        $response = $this->request($this->bitrixCode, $param);

        $total = $response->toArray()['data']['total'];
        $calls = $response->toArray()['data']['result'];
        $next = $response->toArray()['data']['next'] ?? 0;
//dd($response->toArray());
        $response = $this->dependenciesRequest($calls);

        if ($next) {
            $param['query']['start'] = $next;
            $this->requestCalls($param);
        }

        array_unshift($response, $total);

        return $response;
    }

    private function dependenciesRequest($calls): array
    {
        $users = array_map(function ($item) {
            return $item['PORTAL_USER_ID'];
        }, $calls);
        $contacts = array_map(
            fn($item) => $item['CRM_ENTITY_ID'],
            Arr::where($calls, fn($item) => $item['CRM_ENTITY_TYPE'] === 'CONTACT')
        );
        $leads = array_map(
            fn($item) => $item['CRM_ENTITY_ID'],
            Arr::where($calls, fn($item) => $item['CRM_ENTITY_TYPE'] === 'LEAD')
        );
        $activities = array_map(function ($item) {
            return $item['CRM_ACTIVITY_ID'];
        }, $calls);

        $param = [
            'domain' => $this->bitrixCode->domain,
            'query' => [
                'batch' => [
                    'user' => [
                        'method' => 'user.get',
                        'params' => ['ID' => $users]
                    ],
                    'contacts' => [
                        'method' => 'crm.contact.list',
                        'params' => ["filter" => ['ID' => $contacts]]
                    ],
                    'leads' => [
                        'method' => 'crm.lead.list',
                        'params' => ["filter" => ['ID' => $leads]]
                    ],
                    'activities' => [
                        'method' => 'crm.activity.list',
                        'params' => [
                            'filter' => ["ID" => $activities],
                            'select' => ['*', 'COMMUNICATIONS']
                        ]
                    ],
                ]
            ]
        ];

        $response = $this->batchRequest($this->bitrixCode, $param);

        $results = $response->toArray()['data']['result']['result'];
        $results_total = $response->toArray()['data']['result']['result_total'];

        $response = collect($calls)->transform(function ($item) use ($results) {
            $item['USER'] = Arr::first($results['user'] ?? [], function ($user) use ($item) {
                return $item['PORTAL_USER_ID'] === $user['ID'];
            });
            $item['CONTACT'] = Arr::first($results['contacts'] ?? [], function ($contact) use ($item) {
                return $item['CRM_ENTITY_ID'] === $contact['ID'];
            });
            $item['LEAD'] = Arr::first($results['leads'] ?? [], function ($contact) use ($item) {
                return $item['CRM_ENTITY_ID'] === $contact['ID'];
            });
            $item['ACTIVITY'] = Arr::first($results['activities'] ?? [], function ($activity) use ($item) {
                return $item['CRM_ACTIVITY_ID'] === $activity['ID'];
            });
            return $item;
        });

        $this->saveCalls($this->bitrixCode, $response->filter(fn($item) => !empty($item['ACTIVITY']) && $item['CRM_ENTITY_ID']));


        return [$results_total, $response];
    }

    private function requestSalesManagers(array $params)
    {
        $depResponse = $this->request($this->bitrixCode, [
            'domain' => $this->bitrixCode->domain,
            'method' => 'department.get',
            'query' => [
                'params' => [],
            ],
        ]);
        $departments = $depResponse->toArray()['data']['result'];

        $this->runManagersRequest($params, $departments);

    }

    function runManagersRequest(array $param, array $departments)
    {
        $response = $this->request($this->bitrixCode, $param);
        $total = $response->toArray()['data']['total'];
        $next = $response->toArray()['data']['next'] ?? 0;

        $managers = collect($response->toArray()['data']['result'])->transform(function ($item) use ($departments) {
            $item['DEPARTMENT'] = Arr::first($departments, fn($dep) => in_array($dep['ID'], $item['UF_DEPARTMENT']));
            return $item;
        })->toArray();

        // save managers
        $this->saveSalesManagers($this->bitrixCode, $managers);

        if ($next) {
            $param['query']['start'] = $next;
            $this->requestSalesManagers($param);
        }

        return [$managers, $total];
    }

    /**
     * @param BitrixCode $bitrixCode
     * @param $data
     */
    private function saveCalls(BitrixCode $bitrixCode, $data): void
    {

        // здесь разные звонки с одинаковыми идентификатороми из разных битриксов могут перезаписывать друг друга
        // чтоб не ломать совместимость - оставляем перезапись
        // Если звонок перезаписывает не самого себя, а другой (определяем по интеграции), то удаляем старые привязки таких звонков к проектам

        $insertable = [];
        $recordsIds = [];

        foreach ($data as $datum) {
//Log::info('requestCalls', $datum);
            $insertable[] = [
                'schedule_id' => $this->scheduleId,
                'integration_id' => $bitrixCode->integration->id,
                'record_id' => $datum['ID'],
                'record_created_at' => date('Y-m-d H:i:s', strtotime($datum['CALL_START_DATE'])),
                'record_event_type' => $datum['CALL_TYPE'] == 1 ? 'outbound' : 'inbound',
                'record_responsible_id' => $datum['PORTAL_USER_ID'],
                'record_responsible_name' => $this->getUserFullName($datum),
                'record_element_id' => $datum['CRM_ENTITY_ID'],
                'record_element_name' => $datum['PHONE_NUMBER'] ?? '',
                'record_element_type' => $datum['CRM_ENTITY_TYPE'] === 'CONTACT' ? 1 : 2,
                'record_element_link' => $this->getRecordElementLink($datum),
                'record_status' => $datum['CALL_FAILED_CODE'],
                'record_duration' => gmdate('H:i:s', $datum['CALL_DURATION']),
                'record_file_id' => $datum['RECORD_FILE_ID'],
                'record_link' => $datum['RECORD_FILE_ID'] ? $this->parseFileUrl($datum['RECORD_FILE_ID'], $datum['ACTIVITY']['FILES']) : null,
                'record_source' => 'Bitrix24',
            ];
            $recordsIds[] = $datum['ID'];
        }

        // ищем звонки, которые из др.битрикса(отличается integration_id) и которые будут перезаписаны
        $existsOtherCallsIds = Call::query()
            ->select('id')
            ->whereIn('record_id', $recordsIds)
            ->where('integration_id', '<>', $bitrixCode->integration->id)
            ->pluck('id')
            ->all();

        DB::transaction(function () use ($insertable, $existsOtherCallsIds) {
            if (!empty($existsOtherCallsIds)) {
                // если такие звонки есть, отвязываем их от их проектов
                ProjectCall::query()->whereIn('call_id', $existsOtherCallsIds)->delete();
            }
            Call::upsert($insertable, 'record_id');
        });
    }

    private function getRecordElementLink(array $item)
    {
        $crmEntityType = $item['CRM_ENTITY_TYPE'] ?? null;
        $crmEntityId = $item['CRM_ENTITY_ID'] ?? null;
        if ($crmEntityId) {

            //todo потестить и вырезать

            /* if (key_exists('CONTACT', $item)) {
                return "/crm/contact/details/$crmEntityId/";
            }
            if (key_exists('LEAD', $item)) {
                return "/crm/lead/details/$crmEntityId/";
            } */

            if ($crmEntityType === 'CONTACT') {
                return "/crm/contact/details/$crmEntityId/";
            }
            if ($crmEntityType === 'LEAD') {
                return "/crm/lead/details/$crmEntityId/";
            }
            if ($crmEntityType === 'COMPANY') {
                return "/crm/company/details/$crmEntityId/";
            }
        }
        return '';
    }

    private function getUserFullName($datum)
    {
        $keys = ['NAME', 'LAST_NAME', 'SECOND_NAME'];
        $str = '';
        foreach ($keys as $key) {
            if (isset($datum['USER'][$key])) {
                $str .= "{$datum['USER'][$key]} ";
            }
        }
        return trim($str);
    }

    public function parseFileUrl($recordFileID, $files): string
    {
        $fileUrl = Arr::first($files, function ($item) use ($recordFileID) {
            return $item['id'] === $recordFileID;
        })['url'];
        $parsed = parse_url($fileUrl);
        $fullPath = $parsed['scheme'] . '://' . $parsed['host'] . $parsed['path'];
        $queryString = parse_url($fileUrl, PHP_URL_QUERY);
        parse_str($queryString, $params);


        unset($params['auth']);

        return $fullPath . '?' . http_build_query($params);
    }

    /**
     * @param BitrixCode $amoCode
     * @param $data
     * @return bool
     */
    public function saveSalesManagers(BitrixCode $bitrixCode, array $managers): bool
    {
        $insertable = [];

        foreach ($managers as $datum) {
            $department = $datum['DEPARTMENT']['NAME'] ?? 'Отдел продаж';
            $insertable[] = [
                'schedule_id' => $this->scheduleId,
                'foreign_manager_id' => $datum['ID'],
                'integration_id' => $bitrixCode->integration->id,
                'name' => "{$datum['NAME']} {$datum['LAST_NAME']}",
                'email' => $datum['EMAIL'],
                'is_admin' => false,
                'is_active' => $datum['ACTIVE'],
                'group_id' => $datum['UF_DEPARTMENT'][0] ?? null,
                'group_name' => $department,
                'source' => 'Bitrix24',
            ];
        }

        DB::transaction(function () use ($insertable) {
            SalesManager::upsert($insertable, 'foreign_manager_id');
        });

        return true;
    }
}
