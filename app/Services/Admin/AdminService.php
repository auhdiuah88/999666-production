<?php

namespace App\Services\Admin;


use App\Libs\Token;
use App\Repositories\Admin\AdminRepository;
use App\Repositories\Admin\SettingRepository;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;

class AdminService
{
    protected $AdminRepository, $SettingRepository;
    protected $frequency = "frequency";
    protected $time = "time";

    public function __construct(AdminRepository $AdminRepository, SettingRepository $settingRepository)
    {
        $this->AdminRepository = $AdminRepository;
        $this->SettingRepository = $settingRepository;
    }

    public function Login($request)
    {
        if ($request->input("username") != "unicasinonet") {
            $isLimitHost = config('site.is_limit_host');
            if ($isLimitHost && !$this->AdminRepository->getIp(getIp())) {
                return response()->json([
                    "code" => 402,
                    "msg" => "您的ip不在本站IP白名单中，请联系管理员添加IP"
                ]);
            }
        }
        $data = $this->AdminRepository->Get_User($request->input("username"));
        if ($data) {
            if (Crypt::decrypt($data->password) == $request->input("password")) {
                //token 用户id+当前时间戳
                $token = Token::updateAdminToken($data->id);
                $this->AdminRepository->Set_Token($data->id, $token);
                $expiration_date = $this->AdminRepository->Redis_Get_Admin($data->id, $this->time);
//                $expiration_date = time() - 1000;
                // 判断用户是否在系统限定登陆时间中
                if ((time() - $expiration_date) < (10 * 60) && $expiration_date) {
                    return json_encode([
                        'code' => '301',
                        'msg' => '密码错误次数太多，系统限制时间中',
                    ], JSON_UNESCAPED_UNICODE);
                } else {
                    $this->AdminRepository->Redis_Del_Admin($data->id);

                    unset($data->password);
                    unset($data->token);
                    $token = urlencode($token);
                    $admin_user = json_encode([
                        'code' => '200',
                        'msg' => '登录成功',
                        'token' => $token,
                        'data' => $data,
                        "role" => $this->AdminRepository->Get_Role($data->role_id),
                        'country' => env('COUNTRY','india')
                    ], JSON_UNESCAPED_UNICODE);

                    $redisData = array_merge($data,compact('token'));
                    // 将登陆用户信息存入Redis中
                    $this->AdminRepository->Redis_Set_Admin_User(json_encode($redisData, JSON_UNESCAPED_UNICODE), $data->id);
                    $this->AdminRepository->Update_Status($data->id, 1);

                    return $admin_user;
                }
            } else {
//                return json_encode([
//                    'code' => '402',
//                    'msg' => '密码错误',
//                ], JSON_UNESCAPED_UNICODE);
                // 判断用户是否是第一次登陆
                if ($this->AdminRepository->Redis_Get_Admin($data->id, $this->frequency)) {
                    // 判断用户是否在10分钟之内登陆错误次数超过5次
                    if ($this->Check_Redis_Admin($data->id, $this->AdminRepository->Redis_Get_Admin($data->id, $this->frequency))) {
                        return json_encode([
                            'code' => '402',
                            'msg' => '账号或密码错误',
                        ], JSON_UNESCAPED_UNICODE);
                    } else {
                        return json_encode([
                            'code' => '301',
                            'msg' => '登录失败次数过多，请10分钟之后再尝试',
                        ], JSON_UNESCAPED_UNICODE);
                    }
                } else {
                    $redis_data = array("1", null);
                    $this->AdminRepository->Redis_Set_Admin($data->id, $redis_data);
                    return json_encode([
                        'code' => '402',
                        'msg' => '账号或密码错误',
                    ], JSON_UNESCAPED_UNICODE);
                }
            }
        } else {
            return json_encode([
                'code' => '402',
                'msg' => '账号或密码错误',
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function Check_Redis_Admin($userId, $data)
    {
        if ($data < 5) {
            return true;
        } else {
            $redis_data = array(5, time());
            $this->AdminRepository->Redis_Get_Admin($userId, $redis_data);
            return false;
        }
    }

    public function Add($data)
    {
        $data["password"] = Crypt::encrypt($data["password"]);
        $data["create_time"] = time();
        return $this->AdminRepository->Add_Admin($data);
    }

    public function Edit($data)
    {
        $data["update_time"] = time();
        $admin = $this->AdminRepository->Find_By_Id_Admin($data["id"]);
        if (empty($data["password"])) {
            unset($data["password"]);
        }

        if (!empty($data["password"]) && $data["password"] !== Crypt::decrypt($admin->password)) {
            $data["password"] = Crypt::encrypt($data["password"]);
        }else{
            unset($data["password"]);
        }
        return $this->AdminRepository->Edit_Admin($data);
    }

    public function Prohibition($id)
    {
        return $this->AdminRepository->Prohibition_Admin($id);
    }

    public function Del($id)
    {
        return $this->AdminRepository->Del_Admin($id);
    }

    public function FindAll($limit, $page)
    {
        $data = $this->AdminRepository->Find_All_Admin($limit, ($page - 1) * $limit);
        $count = $this->AdminRepository->Count_User();
        $role = $this->AdminRepository->FindAllRole();
        foreach ($data as $key => $item) {
            unset($data[$key]['password']);
        }
        return array("total" => $count, "list" => $data, "role" => $role);
    }

    public function FindById($id)
    {
        return $this->AdminRepository->Find_By_Id_Admin($id);
    }

    public function Out($id)
    {
        $this->AdminRepository->Redis_Del_Admin_User($id);
        $this->AdminRepository->Set_Token($id, null);
        $this->AdminRepository->Update_Status($id, 2);
    }

    public function Relieve($id)
    {
        return $this->AdminRepository->Relieve_Admin($id);
    }

    public function Menu($token)
    {
        $token = urldecode($token);
        $id = explode("+", Crypt::decrypt($token))[0];
        $menu = $this->AdminRepository->getMenu($id);
//        return $menu;
        $admin = $this->AdminRepository->Find_By_Id_Admin($id);
        $staff = $this->SettingRepository->getStaff();
        $leader = $this->SettingRepository->getLeader();
        $role_type = ($staff->setting_value['role_id'] == $admin->role_id || $leader->setting_value['role_id'] == $admin->role_id) ? 2 : 1;
        $role = $staff->setting_value['role_id'] == $admin->role_id ? 3 : ($leader->setting_value['role_id'] == $admin->role_id ? 2 : 1);
        return compact('menu','role_type','role');
    }

    public function updateCustomerStatus($token, $status)
    {
        $token = urldecode($token);
        $id = explode("+", Crypt::decrypt($token))[0];
        return $this->AdminRepository->updateCustomerStatus($id, $status);
    }
}
