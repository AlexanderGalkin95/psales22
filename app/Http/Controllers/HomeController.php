<?php

namespace App\Http\Controllers;

use App\Helpers\SMS;
use App\Models\SmsCodeHistory;
use App\Models\User;
use App\Notifications\VerificationCodeNotification;
use App\Providers\RouteServiceProvider;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class HomeController extends Controller
{
    use ThrottlesLogins, AuthenticatesUsers;
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    const MAX_SMS_SEND_ATTEMPTS = 3;
    public int $maxAttempts = 3;

    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index(): Renderable
    {
        return view('home');
    }

    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function spa(): Renderable
    {
        return view('index');
    }
    public function blank()
    {
        return view('blank');
    }

    public function userBlocked()
    {
        return view('auth.user_blocked');
    }

    public function securityPage(Request $request)
    {
        $user = Auth::user();
        $history = SmsCodeHistory::where('user_id', $user->id)
            ->where('is_current', true)
            ->first();

        if ($history === null){
            Auth::logout();
            return redirect('/login');
        }

        $attempts = self::MAX_SMS_SEND_ATTEMPTS > $history->sms_send_attempts;

        $message = $attempts ? '' : "Привышено допустимое число отправок SMS кода.";

        return view('auth.sms_code_check', ['message'=>$message]);
    }

    public function checkSmsCode(Request $request)
    {
        $user = Auth::user();
        if ($this->hasManySmsCodeAttempts($user->email, $request)) {
            $user->block();
            return redirect('/user/blocked');
        }

        $this->incrementSmsCodeAttempts($user->email, $request);

        $history = SmsCodeHistory::where('user_id', $user->id)
            ->where('is_current', true)
            ->first();

        if ($history === null){
            return redirect('/security/check')
                ->withErrors(['sms_code' => 'Введенный некорректный код.'])
                ->withInput();
        }

        $history->increment('attempts');

        $validator = Validator::make($request->all(), [
            'sms_code' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return redirect('/security/check')
                ->withErrors($validator)
                ->withInput();
        }

        $current = SmsCodeHistory::where('user_id', $user->id)
            ->where('sms_code', $request->get('sms_code'))
            ->where('is_current', true)
            ->first();

        if ($current === null){
            return redirect('/security/check')
                ->withErrors(['sms_code' => 'Введенный некорректный код.'])
                ->withInput();
        }

        if ($current->hasExpired()) {
            SMS::sendVerificationCode($user);

            return redirect('/security/check')
                ->withErrors(['sms_code' => 'Введенный код уже просрочен. Новый код уже отправлен на ваш номер.'])
                ->withInput();
        }

        $current->update([
            'status' => true,
            'is_current' => false
        ]);

        $this->clearSmsCodeAttempts($user->email, $request);

        return redirect(RouteServiceProvider::HOME);
    }

    private function clearSmsCodeAttempts($email, $request)
    {
        $this->limiter()->clear(Str::lower($email).'|'.$request->ip());
    }

    private function hasManySmsCodeAttempts($email, $request): bool
    {
        return $this->limiter()->tooManyAttempts(
            Str::lower($email).'|'.$request->ip(),
            $this->maxAttempts()
        );
    }

    private function incrementSmsCodeAttempts($email, $request)
    {
        $this->limiter()->hit(
            Str::lower($email).'|'.$request->ip(),
            $this->decayMinutes() * 60);
    }
}
