<?php

namespace App\Helpers;

use App\Models\SmsCodeHistory;
use App\Notifications\VerificationCodeNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Utility class for helper functions
 */
class SMS
{
    public static function sendVerificationCode($user)
    {
        if(config('app.two_factor_authentication_mode_enabled', false) && $user->duo) {

            $expired = SmsCodeHistory::where('user_id', $user->id)
                ->where('is_current', true)
                ->where('expires_in', '<', DB::raw("now()"))
                ->first();

            if ($expired === null) {
                $history = SmsCodeHistory::where('user_id', $user->id)
                    ->where('is_current', true)
                    ->first();
                if ($history === null) {
                    $smsCode = rand(100000, 999999);

                    SmsCodeHistory::create([
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'sms_code' => $smsCode,
                        'status' => false,
                        'is_current' => true,
                        'sms_send_attempts' => 1,
                        'expires_in' => DB::raw("now() + interval '15 minutes'"),
                    ]);
                } else {
                    $smsCode = $history->sms_code;
                    $history->increment('sms_send_attempts');
                }
            } else {
                $smsCode = rand(100000, 999999);
                $expired->update([
                    'sms_code' => $smsCode,
                    'sms_send_attempts' => $expired->sms_send_attempts + 1,
                    'expires_in' => DB::raw("now() + interval '15 minutes'"),
                ]);
            }

            $user->notify(new VerificationCodeNotification($smsCode));
        }
    }
}
