<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateIntegrationCheckRequest;
use App\Models\RefIntegration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class IntegrationController extends Controller
{
    public function importIntegrationCalls(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_type' => 'required|integer|exists:ref_integrations,id',
            'date_from' => 'date|nullable',
            'date_to' => 'date|nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([]);
        }

        $reference = RefIntegration::find($request->input('project_type'));
        $integrable = resolve($reference->type)
            ->where('domain', '=', $request->input('integration_domain'))
            ->first();

        $currentDate = now();
        $params = ['--from' => $currentDate, '--to' => $currentDate];
        if ($reference->system_name === 'amo_crm') {
            Artisan::queue('amo:calls', array_merge($params, ['--amo_code_id' => $integrable->id]));
        }

        if ($reference->system_name === 'bitrix_24') {
            Artisan::queue('bitrix:calls', array_merge($params, ['--bitrix_code_id' => $integrable->id]));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Выгрузка звонков запущена'
        ]);
    }

    public function checkIntegrationAccess(ValidateIntegrationCheckRequest $request): JsonResponse
    {
        $reference = RefIntegration::find($request->input('project_type'));

        $integrable = resolve($reference->type)
            ->where('domain', '=', $request->input('integration_domain'))
            ->first();

        $integrableType = Str::studly($reference->system_name);
        $message = "Не удается найти данные интеграции для текущего домена. Переподключите или переустановите интеграцию в $integrableType!";
        if (empty($integrable)) {
            return response()->json([
                'status'  => 'error',
                'fields' => [
                    //'integration_domain' => [$message]
                    'integration_domain' => ['Интеграция с данным доменом отсутствует (*_codes)']
                ]
            ], 422);
        }
        if ($integrable->token === null) {
            //$integrable->reportConnectionError($integrable, $message);

            return response()->json([
                'status'  => 'error',
                'fields' => [
                    //'integration_domain' => [$message]
                    'integration_domain' => ['Интеграция найдена, но отсутствует токен (*_tokens)']
                ]
            ], 422);
        }

        try {
            if ($integrable->token->hasExpired()) {
                $integrable->requestFreshToken();
            }
        } catch (\Exception $e) {
            report($e);
            $integrable->reportConnectionError($integrable, $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
                'fields' => [
                    'integration_domain' => [$e->getMessage()]
                ]
            ], 422);
        }

        return response()->json([
            'status'  => 'success',
            'code'    => 2,
            'message' => "Связь с $integrableType интеграцией установлена успешно.",
        ]);
    }
}
