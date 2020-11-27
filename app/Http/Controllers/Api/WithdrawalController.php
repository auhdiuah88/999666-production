<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Services\Api\WithdrawalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WithdrawalController extends Controller
{
    private $WithdrawalService;

    public function __construct(WithdrawalService $withdrawalService)
    {
        $this->WithdrawalService = $withdrawalService;
    }

    /**
     * 提现申请记录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getRecords(Request $request)
    {
        $this->WithdrawalService->getRecords($request->header("token"));
        return $this->AppReturn(
            $this->WithdrawalService->_code,
            $this->WithdrawalService->_msg,
            $this->WithdrawalService->_data
        );
    }

    /**
     * 提现申请
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addRecord(Request $request)
    {
        $data = $request->post();
        $rules = [
            "bank_id" => "required",
            "money" => "required"
        ];
        $massages = [
            "bank_id.required" => "银行卡id不能为空",
            "money.required" => "提现金额不能为空"
        ];
        $validator = Validator::make($data, $rules, $massages);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        $this->WithdrawalService->addRecord($data, $request->header("token"));
        return $this->AppReturn(
            $this->WithdrawalService->_code,
            $this->WithdrawalService->_msg
        );
    }

    /**
     * 查询申请不通过理由
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getMessage(Request $request)
    {
        $data = $request->post();
        $rules = [
            "id" => "required"
        ];
        $massages = [
            "id.required" => "提现申请记录id不能为空"
        ];
        $validator = Validator::make($data, $rules, $massages);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        $this->WithdrawalService->getMessage($data["id"]);
        return $this->AppReturn(
            $this->WithdrawalService->_code,
            $this->WithdrawalService->_msg,
            $this->WithdrawalService->_data
        );
    }

    /**
     * 代理申请提现接口
     * @param Request $request
     */
    public function agentWithdrawal(Request $request)
    {
        $data = $request->post();
        $rules = [
            "bank_id" => "required",
            "money" => "required"
        ];
        $massages = [
            "bank_id.required" => "银行卡id不能为空",
            "money.required" => "提现金额不能为空"
        ];
        $validator = Validator::make($data, $rules, $massages);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        $this->WithdrawalService->addAgentRecord($data, $request->header("token"));
        return $this->AppReturn(
            $this->WithdrawalService->_code,
            $this->WithdrawalService->_msg
        );
    }

    public function getAgentWithdrawalRecord(Request $request)
    {
        $this->WithdrawalService->getAgentWithdrawalRecord($request->header("token"));
        return $this->AppReturn(
            $this->WithdrawalService->_code,
            $this->WithdrawalService->_msg,
            $this->WithdrawalService->_data
        );
    }

    public function getAgentRewardRecord(Request $request)
    {
        $this->WithdrawalService->getAgentRewardRecord($request->header("token"));
        return $this->AppReturn(
            $this->WithdrawalService->_code,
            $this->WithdrawalService->_msg,
            $this->WithdrawalService->_data
        );
    }
}