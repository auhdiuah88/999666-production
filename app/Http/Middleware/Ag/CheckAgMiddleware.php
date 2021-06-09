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
           if(strpos($request->path(),'m-')){
               return response()->redirectTo(url('ag/m-login'));
           }else{
               return response()->redirectTo(url('ag/index'));
           }
        }
        return $next($request);
    }

}
