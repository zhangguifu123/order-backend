<?php

namespace App\Http\Middleware\Auth;

use Closure;

class ManagerCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $isManager = $request->input('isManager');
        if ($isManager != 1) {
            return response(msg(12, __LINE__));
        }
        return $next($request);
    }
}
