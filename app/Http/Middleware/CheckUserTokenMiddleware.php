<?php

namespace App\Http\Middleware;

use App\Repositories\Api\UserRepository;
use Closure;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class CheckUserTokenMiddleware
{
    protected $UserRepository;


    public function __construct(UserRepository $userRepository)
    {
        $this->UserRepository = $userRepository;

    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $token = $request->header("token");


        if (empty($token)) {
            return response()->json([
                "code" => 401,
//                "msg" => "缺少token令牌"
                "msg" => "请登录"
            ]);
        }

        $token1 = $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));
        if(count($data) < 1){
            return response()->json([
                "code" => 401,
//                "msg" => "token验证失败"
                "msg" => "请登录"
            ]);
        }

        $user_id = $data[0];
        $cache_token = cache()->get(md5('usertoken'.$user_id));
        if(!$cache_token || $cache_token != $token1){
            return response()->json([
                "code" => 401,
//                "msg" => "token验证失败"
                "msg" => "Login failed. Please login again"
            ]);
        }
        if (!$user = $this->UserRepository->cacheUser($user_id)) {
            return response()->json([
                "code" => 401,
//                "msg" => "token验证失败"
                "msg" => "请登录"
            ]);
        }


        $request->attributes->add(['userInfo'=>$user->toArray()]);

        return $next($request);
    }
}
