<?php

namespace App\Services\AmoCRM;


class AmoCRMClient
{

    private array $headers = [];

    /**
     * Create POST request to AmoCRM API
     *
     * @param string $domain
     * @param string|null $uri
     * @param array $post_params
     * @return AmoCRMResponse
     * @throws Exceptions\AmoCRMHttpException
     */
    protected function authResponse(string $domain, string $uri = null, array $post_params = []): AmoCRMResponse
    {
        return AmoCRMRequest::makeRequest($this->getUrl($domain, $uri), [
            'headers' => $this->defaultHeaders(),
            'json' => $post_params
        ], 'post');
    }

    protected function requestPostResponse(string $domain, string $uri = null, array $post_params = []): AmoCRMResponse
    {
        return AmoCRMRequest::makeRequest($this->getUrl($domain, $uri), [
            'headers' => $this->getHeaders(),
            'json' => $post_params
        ], 'post');
    }

    protected function requestGetResponse(string $domain, string $uri = null, array $params = []): AmoCRMResponse
    {
        return AmoCRMRequest::makeRequest($this->getUrl($domain, $uri), [
            'headers' => $this->getHeaders(),
            'json' => $params
        ], 'get');
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
            'User-Agent' => 'amoCRM-oAuth-client/1.0'
        ];
    }

}
