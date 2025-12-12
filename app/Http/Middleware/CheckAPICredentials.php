<?php

namespace App\Http\Middleware;

use App\Exceptions\UserBlockedException;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class CheckAPICredentials
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws UserBlockedException
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->is('api/ext/login')
            ? User::where('email', $request->get('email'))->first()
            : auth('api')->user();

        if ($user && $user->is_blocked) {
            if ($user->tokens) {
                foreach ($user->tokens as $token) {
                    $token->delete();
                }
            }
            throw new UserBlockedException('Пользователь заблокирован', 6, 'user_blocked', 429);
        }

        return $next($request);
    }
}
