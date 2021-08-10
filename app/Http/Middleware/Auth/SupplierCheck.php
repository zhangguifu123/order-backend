<?php

namespace App\Http\Middleware\Auth;

use Closure;

class SupplierCheck
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
        $supplier = $request->input('supplier');
        if (!isset($supplier) || empty($supplier)) {
            return response(msg(12, __LINE__));
        }
        return $next($request);
    }
}
