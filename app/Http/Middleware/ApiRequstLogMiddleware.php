<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ApiRequstLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $start_time = microtime(true);
        $params = $request->all();
        $logData = [
            'request_params' => json_encode($params),
            'ip' => $request->getClientIp() ?? "",
            'c_time' => time(),
            'path' => $request->path()
        ];
        /**
         * @var  $response Illuminate\Http|Response
         */
        $response = $next($request);
        $end_time = microtime(true);
        $logData['response_data'] = $response->getContent() ?? "";
        $logData['exec_time'] = intval($end_time * 10000) - intval($start_time * 10000); //微妙
        DB::table('api_log')->insert([$logData]);
        return $response;
    }
}
