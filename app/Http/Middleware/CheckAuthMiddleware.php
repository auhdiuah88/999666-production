<?php

namespace App\Http\Middleware;

use App\Repositories\Admin\AdminRepository;
use App\Repositories\Admin\JurisdictionRepository;
use App\Repositories\Admin\RoleRepository;
use Closure;
use Illuminate\Support\Facades\Crypt;

class CheckAuthMiddleware
{
    protected $repository, $AdminRepository, $RoleRepository;

    public function __construct(AdminRepository $adminRepository, JurisdictionRepository $repository, RoleRepository $roleRepository)
    {
        $this->AdminRepository = $adminRepository;
        $this->repository = $repository;
        $this->RoleRepository = $roleRepository;
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
        $path = substr($request->path(), 3);
        $token = urldecode($request->header("token"));
        $admin = explode("+", Crypt::decrypt($token));
        $data = $this->repository->Get_Jurisdiction_By_Path($path);
        if ($data) {
            $roleId = $this->AdminRepository->Find_By_Id_Admin($admin[0])->role_id;
            $jurisdiction = $this->RoleRepository->Find_Jurisdiction_By_Id($roleId)->jurisdiction;
            if (!strstr($jurisdiction, $data->id . "")) {
                return response()->json([
                    "code" => 403,
                    "msg" => "您没有访问该接口的权限"
                ]);
            }
        }
        return $next($request);
    }
}
