<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class AdminHandleLogMiddleware
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
        $admin_id = $request->get('admin_id',0);
        $params = $request->all();
        $uri = $request->path();
        $method = $request->method();
        Log::channel('admin_handle')->info($uri,compact('admin_id','params','uri','method'));
        return $next($request);
    }
}
