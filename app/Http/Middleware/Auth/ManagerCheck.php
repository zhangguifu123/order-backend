<?php

namespace App\Http\Middleware\Auth;

use App\User;
use Closure;

class ManagerCheck
{
    /**
     * @var User
     */
    private $model;

    public function __construct()
    {
        $this->model = new User();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $isManager = $request->header('Authorization');
        $Authorization    = substr($isManager, 7);
        $status     = $this->model::query()->where('api_token', $Authorization)->first()->status;
        if ($status != 1) {
            return response(msg(12, __LINE__));
        }
        return $next($request);
    }
}
