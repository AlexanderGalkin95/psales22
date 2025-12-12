<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Bitrix\Facades\BitrixHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BitrixController extends Controller
{
    /**
     * call where install application even url
     * only for rest application, not webhook
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function install(Request $request): JsonResponse
    {
        $result = [
            'rest_only' => true, // Информационный флажок о том, что используем не "webhook"
            'install' => false
        ];

        if($request->input('event') === 'ONAPPINSTALL' && !empty($request->input('auth'))) {
            $result['install'] = BitrixHelper::savePendingToken($request);
        }

        return response()->json([
            'message' => 'Success',
            'response' => $result
        ], 200);

    }
}

