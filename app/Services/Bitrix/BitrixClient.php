<?php

namespace App\Services\Bitrix;


class BitrixClient
{
    const BATCH_COUNT    = 50;
    const TYPE_TRANSPORT = 'json';

    private array $headers = [];


    /**
     * @param string $domain
     * @param string|null $uri
     * @param array $post_params
     * @return BitrixResponse
     */

    protected function requestResponse(string $domain, string $uri = null, array $post_params = []): BitrixResponse
    {
        return BitrixRequest::makeRequest($this->getUrl($domain, $uri), [
            'headers' => $this->getHeaders(),
            'json' => $post_params
        ], 'post');
    }

    protected function getUrl($domain, $uri): string
    {
        return 'https://' . $domain . $uri;
    }

    protected function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * @return array
     */
    protected function getHeaders(): array
    {
        return array_merge($this->defaultHeaders(), $this->headers);
    }

    private function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => 'Bitrix24 CRest PHP 1.36'
        ];
    }

}
