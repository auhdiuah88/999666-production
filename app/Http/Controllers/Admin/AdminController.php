<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\AdminService;

class AdminController extends Controller
{
    private $AdminService;

    public function __construct(AdminService $AdminService)
    {
        $this->AdminService = $AdminService;
    }

    public function Login(Request $request)
    {
        if (empty($request->input("username")) || empty($request->input("password"))) {
            return json_encode([
                'code' => '401',
                'msg' => '用户名或密码不能为空',
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return $this->AdminService->Login($request);
        }
    }

    public function Add(Request $request)
    {
        if ($this->AdminService->Add($request->post())) {
            return json_encode([
                "code" => 200,
                "msg" => "添加成功"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode([
                "code" => 402,
                "msg" => "添加失败"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function Edit(Request $request)
    {
        if ($this->AdminService->Edit($request->post())) {
            return json_encode([
                "code" => 200,
                "msg" => "编辑成功"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode([
                "code" => 402,
                "msg" => "编辑失败"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function Prohibition(Request $request)
    {
        if ($this->AdminService->Prohibition($request->input("id"))) {
            return json_encode([
                "code" => 200,
                "msg" => "封禁成功"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode([
                "code" => 402,
                "msg" => "封禁失败"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function Del(Request $request)
    {
        if ($this->AdminService->Del($request->input("id"))) {
            return json_encode([
                "code" => 200,
                "msg" => "删除成功"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode([
                "code" => 402,
                "msg" => "删除失败"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function FindAll(Request $request)
    {
        $data = $this->AdminService->FindAll($request->get("limit"), $request->get("page"));
        return json_encode([
            "code" => 200,
            "msg" => "查询成功",
            "data" => $data
        ], JSON_UNESCAPED_UNICODE);
    }

    public function FindById(Request $request)
    {
        $data = $this->AdminService->FindById($request->input("id"));
        unset($data->password);
        return json_encode([
            "code" => 200,
            "msg" => "查询成功",
            "data" => $data
        ], JSON_UNESCAPED_UNICODE);
    }

    public function Out(Request $request)
    {
        $this->AdminService->Out($request->input("id"));
        return json_encode([
            "code" => 200,
            "msg" => "退出成功"
        ], JSON_UNESCAPED_UNICODE);
    }

    public function Relieve(Request $request)
    {
        if ($this->AdminService->Relieve($request->input("id"))) {
            return json_encode([
                "code" => 200,
                "msg" => "解除成功"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode([
                "code" => 402,
                "msg" => "解除失败"
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function Menu(Request $request)
    {
        $data = $this->AdminService->Menu($request->header("token"));
        return json_encode([
            "code" => 200,
            "msg" => "查询成功",
            "data" => $data
        ], JSON_UNESCAPED_UNICODE);
    }

    public function UpdateCustomerStatus(Request $request)
    {
        if ($this->AdminService->updateCustomerStatus($request->header("token"), $request->post("status"))) {
            return $this->AppReturn(200, "上线成功");
        } else {
            return $this->AppReturn(402, "上线失败");
        }
    }
}
