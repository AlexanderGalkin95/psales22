<?php

namespace App\Services\Bitrix;


use App\Services\Bitrix\Exceptions\BitrixException;
use Psr\Http\Message\ResponseInterface;

class BitrixResponse
{
    protected ResponseInterface $response;
    /**
     * @var array
     */
    private $output = [];


    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;

        $this->parseResponse();
    }

    /**
     * Parse response
     */
    private function parseResponse(): void
    {
        $status = $this->response ? $this->response->getStatusCode() : 500;
        $response = $this->response ? $this->response->getBody()->getContents() : null;

        $response = json_decode($response, true);
        if (!empty($response['error'])) {
            $arErrorInform = [
                'expired_token'          => 'expired token, cant get new auth? Check access oauth server.',
                'invalid_token'          => 'invalid token, need reinstall application',
                'invalid_grant'          => 'invalid grant, check out define C_REST_CLIENT_SECRET or C_REST_CLIENT_ID',
                'wrong_client'           => 'wrong client, check out define C_REST_CLIENT_SECRET or C_REST_CLIENT_ID',
                'QUERY_LIMIT_EXCEEDED'   => 'Too many requests, maximum 2 query by second',
                'ERROR_METHOD_NOT_FOUND' => 'Method not found! You can see the permissions of the application: CRest::call(\'scope\')',
                'NO_AUTH_FOUND'          => 'Some setup error b24, check in table "b_module_to_module" event "OnRestCheckAuth"',
                'INTERNAL_SERVER_ERROR'  => 'Server down, try later'
            ];
            if(!empty($arErrorInform[$response['error']]))
            {
                throw new BitrixException($arErrorInform[$response['error']], 500);
            }
        }

        $this->output = [
            'status' => $status,
            'data' => $response,
        ];
    }

    public function toSimpleObject()
    {
        return json_decode(json_encode($this->output), false);
    }

    public function json()
    {
        return json_encode($this->output);
    }

    public function toArray(): array
    {
        return $this->output;
    }
}
