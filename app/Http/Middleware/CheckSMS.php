<?php

namespace App\Http\Middleware;

use App\Models\SmsCodeHistory;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSMS
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!config('app.two_factor_authentication_mode_enabled', false))  {
            return $next($request);
        }

        if (Auth::user() === null) {
            return $next($request);
        }

        if (Auth::user()->two_factor_mode === false) {
            return $next($request);
        }

        if (SmsCodeHistory::where('user_id', Auth::user()->id)
            ->where('is_current', true)
            ->count() === 0) {
            if ($request->routeIs('security.check')){
                return redirect(RouteServiceProvider::HOME);
            }
            return $next($request);
        }

        if ($request->routeIs('security.check')
            || $request->routeIs('check.sms_code')
            || $request->routeIs('user_blocked')
            || $request->routeIs('logout')
        ){
            return $next($request);
        }

        return request()->isJson()
            ? response()->json(['message' => 'TWO_FACTOR_AUTHENTICATION => Forbidden.'], 401)
            : redirect(route('security.check'));
    }
}
