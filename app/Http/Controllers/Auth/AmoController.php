<?php

namespace App\Http\Controllers\Auth;

use App\Events\AmoCRMWidgetInstalled;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateParseAmoCodesRequest;
use App\Models\AmoCode;
use App\Services\AmoCRM\Exceptions\AmoCRMException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class AmoController extends Controller
{
    /**
     * @param ValidateParseAmoCodesRequest $request
     * @return JsonResponse
     */
    public function parseAmoCodes(Request $request): JsonResponse
    {
        header('Accept: application/json');

        logger('amo integration hook', $request->all());

//        $validator = Validator::make($request->all(), [
//            'widget' => 'string|nullable',
//            'code' => 'required|string',
//            'referer' => 'required|string',
//            'client_id' => 'required|string',
//            'client_secret' => 'string|nullable',
//            'test' => 'integer|nullable',
//        ]);
//        $validator->validate();
//        if ($validator->fails()) {
//            throw new HttpResponseException(response()->json($validator->errors(), 422));
//        }

        if (!$request->has('referer')) {
            logger("Отсутствует параметр 'referer' (домен)");
            return response()->json();
        }

        $amoCode = AmoCode::where('domain', '=', $request->referer)->first();

        if ($amoCode) {
            logger('amoCode exists ' . $amoCode->id);
        } else {
            logger('amoCode not exists');

            if (!$request->has('key')) {
                logger("Проект с доменом {$request->referer} не может быть установлен, так как в запросе отсутствует параметр 'key' = 'client_secret'. Выполните интеграцию с указанием параметра 'key'!");
                return response()->json();
            }
            if (!$request->has('widget')) {
                logger("Проект с доменом {$request->referer} не может быть установлен, так как в запросе отсутствует параметр 'widget' = 'имя виджета'. Выполните интеграцию с указанием параметра 'widget'!");
                return response()->json();
            }
            $amoCode = new AmoCode();
        }

//        $mapping = [
//            'widget' => 'widget',
//            'code' => 'code',
//            'referer' => 'domain',
//            'client_id' => 'client_id',
//            'key' => 'client_secret',
//        ];
//        $data = [];
//
//        foreach ($request->all() as $key => $value) {
//            if (Arr::exists($mapping, $key)) {
//                $data[$mapping[$key]] = $value;
//            }
//        }
//        $amoCode->fill($data)->save();

        $amoCode->widget = $request->widget ?: $amoCode->widget;
        $amoCode->code = $request->code ?: $amoCode->code;
        $amoCode->domain = $request->referer ?: $amoCode->domain;
        $amoCode->client_id = $request->client_id ?: $amoCode->client_id;
        $amoCode->client_secret = $request->key ?: $amoCode->client_secret;
        $amoCode->save();

        logger('amoCode is changed: ' . (integer)$amoCode->wasChanged());
        logger('amoCode is recentlyCreated: ' . (integer)$amoCode->wasRecentlyCreated);
        logger('amoWidgetInstalled: ' . (integer)(!$amoCode->wasChanged() && !$amoCode->wasRecentlyCreated));

        // а вот этот кусок не работает вообще, вместо него запускается оно же, но из модели AmoCode::boot()->static::updated()
        if (!$amoCode->wasChanged() && !$amoCode->wasRecentlyCreated) {
            event(new AmoCRMWidgetInstalled($amoCode));
        }

        return response()->json([
            'message' => 'Success'
        ], 200);

    }
}
