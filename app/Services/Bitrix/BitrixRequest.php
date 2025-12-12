<?php


namespace App\Services\Bitrix;

use App\Services\Bitrix\Exceptions\BitrixHttpException;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class BitrixRequest
{
    /**
     * @var null|BitrixRequest
     */
    private static ?BitrixRequest $instance = null;
    private Client $httpClient;

    /**
     * gets the instance via lazy initialization (created on first usage)
     */
    private static function getInstance(): BitrixRequest
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
     * @throws BitrixHttpException
     */
    public static function makeRequest(string $url, array $options, string $method = 'get'): BitrixResponse
    {
        return new BitrixResponse(
            $method === 'get'
                ? self::runGetRequest($url, $options)
                : self::runPostRequest($url, $options)
        );
    }

    /**
     * @throws BitrixHttpException
     */
    private static function runGetRequest($url, $options): ResponseInterface
    {
        try {
            return self::getInstance()->getHttpClient()->get($url, $options);
        } catch (Throwable $exception) {
            throw new BitrixHttpException($exception);
        }
    }

    /**
     * @throws BitrixHttpException
     */
    private static function runPostRequest($url, $options): ResponseInterface
    {
        try {
            return self::getInstance()->getHttpClient()->post($url, $options);
        } catch (Throwable $exception) {
            throw new BitrixHttpException($exception);
        }
    }

    private function getHttpClient(): Client
    {
        return $this->httpClient;
    }
}
