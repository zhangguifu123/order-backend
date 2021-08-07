<?php

namespace App\Http\Middleware\Excel;

use Closure;

class CheckExcelsCheck
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
        $file = $request->file('file');
        $ext = $file->getClientOriginalExtension(); // 获取后缀
        $allow_ext = ['xls', 'xlsx'];
        if (!in_array($ext, $allow_ext)) {
            return msg(8, __LINE__);
        }
        return $next($request);
    }
}
