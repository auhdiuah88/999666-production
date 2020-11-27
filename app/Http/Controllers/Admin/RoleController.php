<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected $RoleService;

    public function __construct(RoleService $RoleService)
    {
        $this->RoleService = $RoleService;
    }

    public function Add(Request $request)
    {
        if ($this->RoleService->Add($request->post())) {
            return json_encode([
                'code' => 200,
                'msg' => '添加成功'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode([
                'code' => 402,
                'msg' => '添加失败'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function Edit(Request $request)
    {
        if ($this->RoleService->Edit($request->post())) {
            return json_encode([
                'code' => 200,
                'msg' => '编辑成功'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode([
                'code' => 402,
                'msg' => '编辑失败'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function FindAll(Request $request)
    {
        $data = $this->RoleService->FindAll($request->get("limit"), $request->get("page"));
        return json_encode([
            'code' => 200,
            'msg' => '查询成功',
            "data" => $data
        ], JSON_UNESCAPED_UNICODE);
    }

    public function Del(Request $request)
    {
        if ($this->RoleService->Del($request->input("id"))) {
            return json_encode([
                'code' => 200,
                'msg' => '删除成功'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode([
                'code' => 402,
                'msg' => '删除失败'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function FindById(Request $request)
    {
        $data = $this->RoleService->FindById($request->input("id"));
        return json_encode([
            'code' => 200,
            'msg' => '查询成功',
            "data" => $data
        ], JSON_UNESCAPED_UNICODE);
    }
}