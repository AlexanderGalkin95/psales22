<?php

namespace App\Services\Bitrix\Exceptions;


use GuzzleHttp\Exception\RequestException;
use Throwable;

class BitrixHttpException extends BitrixException
{
    public function __construct(Throwable $exception)
    {
        $code = $exception->getCode();
        $message = $exception->getMessage();
        if ($exception instanceof RequestException && $exception->hasResponse()) {
            [$code, $message] = $this->parseExceptionResponse($exception);
        }
        $previous = null;
        parent::__construct($message, $code, $previous);
    }

    private function parseExceptionResponse($exception): array
    {
        $response = $exception->getResponse();
        $messageBody = json_decode($response->getBody()->getContents());

        return [$response->getStatusCode(), ($messageBody->detail ?? 'Некорректный запрос')
            .' (Подсказка: '.($messageBody->error ?? '-').')' ];
    }
}
