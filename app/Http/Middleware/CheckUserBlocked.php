<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserBlocked
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
        if (Auth::user() === null) {
            return $next($request);
        }

        if (Auth::user()->is_blocked === false){
            if ($request->routeIs('user_blocked')) {
                return redirect(RouteServiceProvider::HOME);
            }
            return $next($request);
        }

        if ($request->routeIs('user_blocked') || $request->routeIs('logout')){
            return $next($request);
        }

        return redirect(route('user_blocked'));
    }
}
