<?php


namespace App\Services\AmoCRM\Exceptions;


use GuzzleHttp\Exception\RequestException;
use Throwable;

class AmoCRMHttpException extends AmoCRMException
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
        $content = $response->getBody()->getContents();
        $messageBody = json_decode($content);

        // если есть подсказка, выводим ее. Если нет - то первые 1000 символов с данными
        if ($messageBody->hint) {
            $message = ($messageBody->detail ?? 'Некорректный запрос') . ' (Подсказка: ' . ($messageBody->hint ?? '-') . ')';
        } else {
            $message = substr($content, 0, 1000);
        }

        return [
            $response->getStatusCode(),
            $message,
        ];
    }
}
