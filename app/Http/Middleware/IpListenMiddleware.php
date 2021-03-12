<?php


namespace App\Http\Middleware;


use Closure;
use Illuminate\Support\Facades\Log;

class IpListenMiddleware
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
        Log::channel('apidebug')->debug('api', ['methods'=>$request->getMethod(), 'path'=>$request->path(), 'time'=>date('Y-m-d H:i:s'), 'ip'=>$request->ip()]);
        return $next($request);
    }

}
