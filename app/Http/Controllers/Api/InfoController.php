<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Services\Api\InfoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InfoController extends Controller
{
    private $InfoService;

    public function __construct(InfoService $infoService)
    {
        $this->InfoService = $infoService;
    }

    /**
     * 获取用户基本信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getInfo(Request $request)
    {
        $this->InfoService->getInfo($request->header("token"));
        return $this->AppReturn(
            $this->InfoService->_code,
            $this->InfoService->_msg,
            $this->InfoService->_data
        );
    }

    /**
     * 获取用户银行卡信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getBanks(Request $request)
    {
        $this->InfoService->getBanks($request->header("token"));
        return $this->AppReturn(
            $this->InfoService->_code,
            $this->InfoService->_msg,
            $this->InfoService->_data
        );
    }

    /**
     * 获取单个银行卡信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getBankById(Request $request)
    {
        $this->InfoService->getBankById($request->get("id"));
        return $this->AppReturn(
            $this->InfoService->_code,
            $this->InfoService->_msg,
            $this->InfoService->_data
        );
    }

    /**
     * 添加银行卡
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addBank(Request $request)
    {
        $data = $request->post();
        $rules = [
            "bank_num" => "required",
            "mail" => "required",
            "phone" => "required",
            "account_holder" => "required"
        ];
        $massages = [
            "bank_num.required" => "银行卡号码不能为空",
            "mail.required" => "邮箱不能为空",
            "phone.required" => "银行卡预留电话号码不能为空",
            "account_holder.required" => "银行卡开户人不能为空",
        ];
        $validator = Validator::make($data, $rules, $massages);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        $this->InfoService->addBank($data, $request->header("token"));
        return $this->AppReturn(
            $this->InfoService->_code,
            $this->InfoService->_msg,
            $this->InfoService->_data
        );
    }

    /**
     * 编辑银行卡
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function editBank(Request $request)
    {
        $data = $request->post();
        $rules = [
            "id" => "required",
            "bank_num" => "required",
            "mail" => "required",
            "phone" => "required",
            "account_holder" => "required"
        ];
        $massages = [
            "id.required" => "银行卡ID不能为空",
            "mail.required" => "邮箱不能为空",
            "bank_opening.required" => "银行卡开户行不能为空",
            "phone.required" => "银行卡预留电话号码不能为空",
            "account_holder.required" => "银行卡开户人不能为空",
        ];
        $validator = Validator::make($data, $rules, $massages);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        $this->InfoService->editBank($data, $request->header("token"));
        return $this->AppReturn(
            $this->InfoService->_code,
            $this->InfoService->_msg,
            $this->InfoService->_data
        );
    }

    /**
     * 删除银行卡
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function delBank(Request $request)
    {
        $data = $request->post();
        $rules = [
            "id" => "required"
        ];
        $massages = [
            "id.required" => "银行卡ID不能为空"
        ];
        $validator = Validator::make($data, $rules, $massages);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        $this->InfoService->delBank($data["id"], $request->header("token"));
        return $this->AppReturn(
            $this->InfoService->_code,
            $this->InfoService->_msg,
            $this->InfoService->_data
        );
    }

    /**
     * 用户修改昵称接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateNickname(Request $request)
    {
        $data = $request->post();
        $rules = [
            "nickname" => "required"
        ];
        $massages = [
            "nickname.required" => "昵称不能为空"
        ];
        $validator = Validator::make($data, $rules, $massages);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        $this->InfoService->updateNickname($data["nickname"], $request->header("token"));
        return $this->AppReturn(
            $this->InfoService->_code,
            $this->InfoService->_msg
        );
    }

    /**
     * 用户修改密码接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        $data = $request->post();
        $rules = [
            "o_password" => "required",
            "l_password" => "required"
        ];
        $massages = [
            "o_password.required" => "原密码不能为空",
            "l_password.required" => "新密码不能为空"
        ];
        $validator = Validator::make($data, $rules, $massages);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        $this->InfoService->updatePassword($data, $request->header("token"));
        return $this->AppReturn(
            $this->InfoService->_code,
            $this->InfoService->_msg
        );
    }
}
