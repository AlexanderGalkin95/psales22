<?php


namespace App\Services\SMSRU;


use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\StreamInterface;

class SMSRUClient
{
    private ?string $baseUrl;

    private Client $client;

    private array $defaultParams = [];

    /**
     * @throws Exception
     */
    public function __construct($config)
    {
        $this->defaultParams = [
            'api_id' => $config['api_id'],
        ];
        $this->setBaseUrl(config('services.smsru.baseUrl'));
        $this->client = new Client();
    }

    /**
     * @param string|null $baseUrl
     * @throws Exception
     */
    public function setBaseUrl(?string $baseUrl): void
    {
        if (empty($baseUrl)) throw new Exception('Url не был указан',422);
        $this->baseUrl = $baseUrl;
    }
    /**
     * @throws GuzzleException
     * @throws Exception
     */
    protected function request($method, $params = []): StreamInterface
    {
        try {
            $response = $this->client->post(
                $this->getUri($method),
                ['query' => array_merge($this->defaultParams, $params)]
            );
            return $response->getBody();
        } catch (Exception $exception) {
            throw new Exception('Сообщение не было доставлено получателю');
        }
    }

    protected function getUri($method): string
    {
        return $this->baseUrl . $method . '&json=1';
    }

    /**
     * @throws GuzzleException
     */
    public function send(Message $message)
    {
        $params = [];
        $params['to'] = $message->geTo();
        $params['text'] = $message->getText();
        if ($message->getFrom()) {
            $params['from'] = $message->getFrom();
        }
        $response = $this->request('sms/send', $params);
        return explode("\n", $response);
    }
}
