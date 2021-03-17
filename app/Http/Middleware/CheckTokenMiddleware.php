<?php

namespace App\Http\Middleware;

use App\Libs\Token;
use App\Repositories\Admin\AdminRepository;
use Closure;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redis;

class CheckTokenMiddleware
{
    protected $repository;

    public function __construct(AdminRepository $repository)
    {
        $this->repository = $repository;
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
        if(config('site.is_limit_host','false'))
        {
            if(!Redis::sismember('WHITE_IPS', getIp()))
            {
                return response()->json([
                    "code" => 403,
                    "msg" => "非法访问"
                ]);
            }
        }
        $token = $request->header("token");
        if (empty($token)) {
            return response()->json([
                "code" => 1001,
                "msg" => "缺少token令牌"
            ]);
        }
        $token = urldecode($token);
        $oldToken = $token;
        $data = explode("+", Crypt::decrypt($token));
        if (!env('IS_DEV',false)) {
            if(!$admin = $this->repository->Redis_Get_Admin_User($data[0])){
                return response()->json([
                    "code" => 1001,
                    "msg" => "token验证失败"
                ]);
            }
            if($oldToken != $admin['token']){
                return response()->json([
                    "code" => 1001,
                    "msg" => "token验证失败."
                ]);
            }
        }
        $request->attributes->add(['admin_id'=>$data[0]]);
        $response = $next($request);

//        $content = $response->getContent()?? [];
//        if($content){
//            $content = json_decode($content,true);
//        }
//        $newToken = Token::updateAdminToken($data[0]);
//        $content['token'] = $newToken;
//        $response->setContent(json_encode($content));
        return $response;
    }
}
