<?php

namespace App\Http\Middleware;

use App\Repositories\Admin\AdminRepository;
use Closure;
use Illuminate\Support\Facades\Crypt;

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
        $token = $request->header("token");
        if (empty($token)) {
            return response()->json([
                "code" => 1001,
                "msg" => "缺少token令牌"
            ]);
        }
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));
//        if (!$this->repository->Redis_Get_Admin_User($data[0])) {
//            return response()->json([
//                "code" => 1001,
//                "msg" => "token验证失败"
//            ]);
//        }
        $request->attributes->add(['admin_id'=>$data[0]]);
        return $next($request);
    }
}
