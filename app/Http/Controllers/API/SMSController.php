<?php

namespace App\Http\Controllers\API;

use App\Helpers\SMS;
use App\Http\Controllers\Controller;
use App\Models\SmsCodeHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SMSController extends Controller
{
    const MAX_SMS_SEND_ATTEMPTS = 3;

    public function requestSmsCode(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $history = SmsCodeHistory::where('user_id', $userId)
            ->where('is_current', true)
            ->first();

        if ($history === null){
            return response()->json([
                'status' => 'error',
                'message' => 'Не удается отправить SMS. Код авторизации не найден.'
            ], 422);
        }

        if(self::MAX_SMS_SEND_ATTEMPTS > $history->sms_send_attempts){
            SMS::sendVerificationCode(Auth::user());

            return response()->json([
                'status' => 'success',
                'message' => 'Код автаризации был отправлен на ваш номер телефона'
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Привышено допустимое число отправок SMS кода.'
        ], 403);
    }
}
