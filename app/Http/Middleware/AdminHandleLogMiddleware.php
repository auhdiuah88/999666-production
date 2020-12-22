<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
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
        $start_time = microtime(true);
        $admin_id = $request->get('admin_id',0);
        $params = $request->all();
        $method = $request->method();
        $logData = [
            'request_params' => serialize($params),
            'admin_id' => $admin_id,
            'method' => $method,
            'ip' => $request->getClientIp() ?? "",
            'c_time' => time(),
            'path' => $request->path()
        ];
        $response = $next($request);
        $end_time = microtime(true);
        $logData['exec_time'] = intval($end_time * 10000) - intval($start_time * 10000); //微妙
        DB::table('admin_operation_log')->insert([$logData]);
        return $response;
    }
}
