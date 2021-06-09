<?php


namespace App\Http\Middleware\Ag;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class CheckAgMiddleware
{

    public function handle($request, Closure $next)
    {
        if(!getAgentUser())
        {
           if(strpos($request->path(),'m-') || strpos($request->path(),'index')){
               return response()->redirectTo(url('ag/m-login'));
           }else{
               return response()->redirectTo(url('ag/index'));
           }
        }
        return $next($request);
    }

}
