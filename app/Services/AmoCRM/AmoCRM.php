<?php


namespace App\Services\AmoCRM;


use App\Services\AmoCRM\Exceptions\AmoCRMException;
use Illuminate\Support\Facades\Validator;

class AmoCRM extends AmoCRMClient
{
    /**
     * Requesting access token for the very first time
     * <code>
     * $params = [
     *      client_id => 'client_id',
     *      client_secret'=> 'client_secret',
     *      grant_type"=> "authorization_code",
     *      code"=> 'code',
     *      redirect_uri'=> 'redirect_uri',
     * ];
     * </code>
     * Requesting a fresh access token using refresh_token
     * <code>
     * $params = [
     *      client_id => 'client_id',
     *      client_secret'=> 'client_secret',
     *      grant_type"=> "refresh_token",
     *      refresh_token"=> 'refresh_token',
     *      redirect_uri'=> 'redirect_uri'
     * ];
     * </code>
     * @param string $domain
     * @param array $params
     * @return AmoCRMResponse
     * @throws AmoCRMException
     */
    public function requestToken(string $domain, array $params): AmoCRMResponse
    {
        $this->validateDomain($domain);

        return $this->authResponse($domain, '/oauth2/access_token', $params);
    }

    /**
     * @throws AmoCRMException
     */
    private function validateDomain($domain): void
    {
        $validator = Validator::make(compact('domain'), [
            'domain' => "required|regex:/^([0-9a-z-_]+)\.amocrm\.ru$/"
        ]);

        if ($validator->fails()) {
            throw new AmoCRMException('Домен имеет неправильный формат (домен должен заканчиваться на .amocrm.ru)', 422);
        }
    }

    private function buildArgs(string $domain, string $uri, array $params, array $headers): array
    {
        $this->validateDomain($domain);

        if (count($headers)) {
            $this->setHeaders($headers);
        }

        $query = $params['filter'] ?? [];
        if (!empty($query)) {
            unset($params['filter']);
        }

        $query = http_build_query($query);

        return [
            $domain,
            "$uri?$query",
            $params
        ];
    }

    /**
     * @param string $domain
     * @param array $params
     * @param array $headers
     * @return AmoCRMResponse
     */
    public function requestCalls(string $domain, array $params = [], array $headers = []): AmoCRMResponse
    {
        $this->validateDomain($domain);

        if (count($headers)) {
            $this->setHeaders($headers);
        }

        $query = $params['filter'] ?? [];
        if (!empty($query)) {
            unset($params['filter']);
        }

        $query = http_build_query($query);

        return $this->requestPostResponse($domain, "/ajax/stats/calls/?$query", $params);
    }

    public function requestPipelines(string $domain, array $params = [], array $headers = []): AmoCRMResponse
    {
        return $this->requestGetResponse(...$this->buildArgs($domain, "/api/v4/leads/pipelines", $params, $headers));
    }

    public function requestSalesManagers(string $domain, array $params = [], array $headers = []): AmoCRMResponse
    {
        return $this->requestGetResponse(...$this->buildArgs($domain, "/api/v4/users", $params, $headers));
    }

}
