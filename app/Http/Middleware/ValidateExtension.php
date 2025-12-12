<?php

namespace App\Http\Middleware;

use App\Exceptions\ExtensionBlockedException;
use App\Models\GoogleExtension;
use Closure;
use Illuminate\Http\Request;

class ValidateExtension
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws ExtensionBlockedException
     */
    public function handle(Request $request, Closure $next)
    {
        $extension = GoogleExtension::where('extension_id', '=', $request->header('extension_id'))->first();

        if ($extension && $extension->is_blocked) {
            throw new ExtensionBlockedException('Расширение заблокировано', 403);
        }
        return $next($request);
    }
}
