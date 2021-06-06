<?php


namespace App\Http\Middleware\Ag;

use Closure;
use Illuminate\Support\Facades\Cache;

class CheckAgMiddleware
{

    public function handle($request, Closure $next)
    {
        if(!Cache::get('user'))
        {
            return response()->redirectTo(url('ag/index'));
        }
        return $next($request);
    }

}
