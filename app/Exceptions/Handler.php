<?php

namespace App\Exceptions;

use App\Mail\ReportException;
use App\Services\AmoCRM\Exceptions\AmoCRMException;
use App\Services\Bitrix\Exceptions\BitrixException;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use Swift_TransportException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (\Throwable $e) {
            if ($this->shouldReport($e)) {

//                if (!$this->shouldNotReportToSysAdmin($e)) {
//                    $this->reportToSysadmin($e);
//                }

//                if (
//                    app()->bound('sentry')
//                    && app()->environment('production')
//                ) {
//                    app('sentry')->captureException($e);
//                }
            }
        });

        $this->renderable(function (\Throwable $exception) {
            if ($exception instanceof AuthenticationException && request()->isJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            if ($exception instanceof ValidationException && request()->isJson()) {
                if ($exception->status === 422) {
                    return response()->json([
                        'status'  => 'error',
                        'code'    => 7,
                        'message' => 'Validation error',
                        'fields'  => $exception->validator->getMessageBag()->getMessages()
                    ], 422);
                }
            }
            if ($exception instanceof AccessDeniedHttpException && request()->isJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                    'status' => 'error',
                ], 403);
            }
            if (
                $exception instanceof HttpException
                && $exception->getMessage() === 'CSRF token mismatch.'
            ) {
                return redirect()
                    ->back()
                    ->withErrors([
                        'global' => 'Ой! Время сеанса истекло. Пожалуйста, попробуйте еще раз зайти в систему!'
                    ]);
            }
            if ($exception instanceof QueryException) {
                if (app()->environment('production')) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Сервер не смог выполнить ваш запрос',
                    ], 500);
                }
            }
        });
    }

    protected array $notReportableToSysAdmin = [
        Swift_TransportException::class,
        AmoCRMException::class,
        BitrixException::class,
    ];

    protected function shouldNotReportToSysAdmin($e): bool
    {
        return ! is_null(Arr::first($this->notReportableToSysAdmin, function ($type) use ($e) {
            return $e instanceof $type;
        }));
    }

    protected function reportToSysadmin($exception)
    {
        $r = request();
        $date = Carbon::now();
        $message =  new \stdClass();
        $message->message = $exception->getMessage();
        $message->line = $exception->getLine();
        $message->trace = $exception->getTraceAsString();
        $message->file = $exception->getFile();
        $message->class = get_class($exception);
        $message->method = $r->method();
        $message->path = $r->getPathInfo();
        $message->query = $r->getQueryString();
        $message->ip = $r->getClientIp();
        $message->uri = $r->getUri();
        $message->origin = $r->header('origin');
        $message->referer = $r->header('referer');
        $message->forwarded = $r->header('X-Forwarded-For');
        $message->agent = $r->header('User-Agent');
        Mail::to(config('mail.sysadmin_email_address'))
            ->queue(new ReportException($message, $date));
    }
}
