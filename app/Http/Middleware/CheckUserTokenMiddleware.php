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
        $i_c = env('IS_CRYPT',false);
        $rtn = [
            "code" => 401,
//                "msg" => "缺少token令牌"
            "msg" => "Please login again"
        ];
        if (empty($token)) {
            if($i_c){
                $rtn = aesEncrypt(json_encode($rtn));
            }
            return response()->json($rtn);
        }

        $token1 = $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));
        if(count($data) < 1){
            if($i_c){
                $rtn = aesEncrypt(json_encode($rtn));
            }
            return response()->json($rtn);
//            return response()->json([
//                "code" => 401,
////                "msg" => "token验证失败"
//                "msg" => "Please login again"
//            ]);
        }

        $user_id = $data[0];
        $cache_token = cache()->get(md5('usertoken'.$user_id));
        if(!env('IS_DEV',false) && (!$cache_token || $cache_token != $token1)){
            $rtn = [
                "code" => 401,
                "msg" => "Login failed. Please login again"
            ];
            if($i_c){
                $rtn = aesEncrypt(json_encode($rtn));
            }
            return response()->json($rtn);
        }

        if (!$user = $this->UserRepository->cacheUser($user_id)) {
            $rtn = [
                "code" => 401,
                "msg" => "Please login again"
            ];
            if($i_c){
                $rtn = aesEncrypt(json_encode($rtn));
            }
            return response()->json($rtn);
//            return response()->json([
//                "code" => 401,
////                "msg" => "token验证失败"
//                "msg" => "Please login again"
//            ]);
        }


        $request->attributes->add(['userInfo'=>$user->toArray()]);

        return $next($request);
    }
}
