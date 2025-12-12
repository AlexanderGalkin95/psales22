<?php


namespace App\Services\Bitrix;


use App\Services\Bitrix\Exceptions\BitrixException;
use Illuminate\Support\Facades\Validator;

class Bitrix extends BitrixClient
{
    public function call($params = []): BitrixResponse
    {
        $headers = $params['headers'] ?? [];
        if (count($headers)) {
            $this->setHeaders($headers);
            unset($params['headers']);
        }

        return $this->sendRequest($params);
    }

    /**
     * @param array $params
     * @param int|null $halt
     * @return BitrixResponse
     *
     * @example $arData:
     * $arData = [
     *      'find_contact' => [
     *          'method' => 'crm.duplicate.findbycomm',
     *          'params' => [ "entity_type" => "CONTACT",  "type" => "EMAIL", "values" => array("info@bitrix24.com") ]
     *      ],
     *      'get_contact' => [
     *          'method' => 'crm.contact.get',
     *          'params' => [ "id" => '$result[find_contact][CONTACT][0]' ]
     *      ],
     *      'get_company' => [
     *          'method' => 'crm.company.get',
     *          'params' => [ "id" => '$result[get_contact][COMPANY_ID]', "select" => ["*"],]
     *      ]
     * ];
     */

    public function callBatch(array $params, ?int $halt = 0): BitrixResponse
    {
        $headers = $params['headers'] ?? [];
        if (count($headers)) {
            $this->setHeaders($headers);
            unset($params['headers']);
        }

        $domain = $params['domain'];
        unset($params['domain']);

        $arDataRest = [];
        $i = 0;
        foreach($params['query']['batch'] as $key => $data) {
            if(!empty($data['method'])) {
                $i++;
                if(static::BATCH_COUNT >= $i) {
                    $arDataRest['cmd'][$key] = $data['method'];
                    if(!empty($data['params'])) {
                        $arDataRest['cmd'][$key] .= '?' . http_build_query($data['params']);
                    }
                }
            }
        }
        unset($params['query']['batch']);
        $arDataRest['halt'] = $halt;
        $arPost = [
            'domain' => $domain,
            'method' => 'batch',
            'query' => $arDataRest
        ];
        $params = array_merge_recursive($params, $arPost);

        return $this->sendRequest($params);
    }

    protected function sendRequest($params): BitrixResponse
    {
        if (isset($params['this_auth']) && $params['this_auth']) {
            $domain = 'oauth.bitrix.info';
            $uri = '/oauth/token';
        } else {
            $method = $params['method'];
            $domain = $params['domain'];
            unset($params['domain']);

            // отключено: домены м.б. разными
            //$this->validateDomain($domain);

            $uri = '/rest/' . $method . '.' . static::TYPE_TRANSPORT;
        }

        $query = $params['query'] ?? [];
        if (!empty($query)) {
            unset($params['query']);
        }

        $query = http_build_query($query);

        return $this->requestResponse($domain, "$uri/?$query", $params);
    }

    /**
     * @deprecated
     * @throws BitrixException
     */
    private function validateDomain($domain): void
    {
        // отключено: домены м.б. разными
        return;
        $validator = Validator::make(compact('domain'), [
            'domain' => "required|regex:/^([0-9a-z-_]+)\.bitrix24\.ru$/"
        ]);

        if ($validator->fails()) {
            throw new BitrixException('Домен имеет неправильный формат (домен должен заканчиваться на .bitrix24.ru)', 422);
        }
    }

}
