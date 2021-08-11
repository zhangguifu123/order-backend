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
        $isManager      = $request->header('Authorization');
        $Authorization  = substr($isManager, 7);
        $supplier       = $this->model::query()->where('api_token', $Authorization)->first()->supplier;
        if (isset($supplier) && !empty($supplier)){
            $request['supplier'] = $supplier;
            return $next($request);
        }
        return msg(13, __LINE__);
    }
}
