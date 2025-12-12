<?php


namespace App\Services\AmoCRM;

use App\Services\AmoCRM\Exceptions\AmoCRMHttpException;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AmoCRMRequest
{
    /**
     * @var null|AmoCRMRequest
     */
    private static ?AmoCRMRequest $instance = null;
    private Client $httpClient;

    /**
     * gets the instance via lazy initialization (created on first usage)
     */
    private static function getInstance(): AmoCRMRequest
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * It is not allowed to call from outside to prevent from creating multiple instances
     */
    private function __construct()
    {
        $this->httpClient = new Client();
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }

    /**
     * prevent from being unserialized (which would create a second instance of it)
     */
    public function __wakeup()
    {
    }

    /**
     * @throws AmoCRMHttpException
     */
    public static function makeRequest(string $url, array $options, string $method = 'get'): AmoCRMResponse
    {
        return new AmoCRMResponse(
            $method === 'get'
                ? self::runGetRequest($url, $options)
                : self::runPostRequest($url, $options)
        );
    }

    /**
     * @throws AmoCRMHttpException
     */
    private static function runGetRequest($url, $options): ResponseInterface
    {
        try {
            return self::getInstance()->getHttpClient()->get($url, $options);
        } catch (Throwable $exception) {
            throw new AmoCRMHttpException($exception);
        }
    }

    /**
     * @throws AmoCRMHttpException
     */
    private static function runPostRequest($url, $options): ResponseInterface
    {
        try {
            return self::getInstance()->getHttpClient()->post($url, $options);
        } catch (Throwable $exception) {
            throw new AmoCRMHttpException($exception);
        }
    }

    private function getHttpClient(): Client
    {
        return $this->httpClient;
    }
}
