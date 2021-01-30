<?php


namespace App\Http\Controllers\Api;


use App\Dictionary\WithdrawalAmount;
use App\Http\Controllers\Controller;
use App\Services\Api\WithdrawalService;
use App\Services\Pay\PayContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            "bank_id.required" => "Bank card id cannot be empty",
            "money.required" => "The withdrawal amount cannot be empty"
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
        $this->WithdrawalService->getAgentRewardRecord($request->header("token"), $request->post("type"));
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
            "bank_id" => "required|integer|min:300|max:25000",
            "money" => "required"
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        if (!$result = $this->WithdrawalService->addAgentRecord($request)) {
            return $this->AppReturn(400, $this->WithdrawalService->_msg, new \StdClass());
        }
        return $this->AppReturn(200, 'ok',$result);
    }

    /**
     * 佣金提现到余额
     * @param Request $request
     * @return WithdrawalController
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function agentApplyBalance(Request $request)
    {
        $data = $request->post();
        $rules = [
            "money" => "required|integer|min:".WithdrawalAmount::MIN."|max:" . WithdrawalAmount::MAX
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        if (!$result = $this->WithdrawalService->applyToBalance($request)) {
            return $this->AppReturn(400, $this->WithdrawalService->_msg, new \StdClass());
        }
        return $this->AppReturn(200, 'ok',$result);
    }

    /**
     * 用户申请提现接口
     */
    public function withdrawalByBank(Request $request) {
        $rules = [
            'money' => "required",
            'bank_id' => "required",
        ];
        $validator = Validator::make($request->post(), $rules);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }

        if (!$result = $this->WithdrawalService->withdrawalOrder($request)) {
            return $this->AppReturn(400, $this->WithdrawalService->_msg, new \StdClass());
        }
        return $this->AppReturn(200, 'ok',  $result);
    }

    /**
     * 用户upi_id提现-请求出金订单
     */
    public function withdrawalByUpiID(Request $request) {
        $rules = [
            'money' => "required",
            'upi_id' => "required",
        ];
        $validator = Validator::make($request->post(), $rules);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        $request->account_holder = 'xxxx';
        $request->bank_number = 'xxxx';
        $request->bank_name = 'xxxx';
        $request->ifsc_code = 'xxxx';
        if (!$result = $this->WithdrawalService->withdrawalOrder($request)) {
            return $this->AppReturn(400, $this->WithdrawalService->_msg, new \StdClass());
        }
        return $this->AppReturn(200, '用户提款-请求出金订单', $result);
    }

    /**
     * 用户银行卡提现-请求出金订单
     */
    public function withdrawalBydai(Request $request) {
        $rules = [
            'money' => "required|integer|min:1",
            'bank_id' => "required",
        ];
        $validator = Validator::make($request->post(), $rules);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        if (!$result = $this->WithdrawalService->withdrawalOrder($request)) {
            return $this->AppReturn(400, $this->WithdrawalService->_msg, new \StdClass());
        }
        return $this->AppReturn(200, '用户提款-请求出金订单', $result);
    }

    /**
     * 提款回调
     */
    public function withdrawalCallback(Request $request)
    {
        try{
            if ($this->WithdrawalService->withdrawalCallback($request)) {
                return $this->WithdrawalService->_msg;
            }
            return $this->WithdrawalService->_msg;
        }catch(\Exception $e){
            Log::channel('kidebug')->error('recharge_callback', ['file'=>$e->getFile(),'line'=>$e->getLine(), 'message'=>$e->getMessage(), 'data'=>$request->all()]);
            return false;
        }
    }

    /**
     * 提现方式
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function withdrawType(Request $request){
        try{
//            $list = config('pay.withdraw');
            $this->WithdrawalService->withdrawType();
            return $this->AppReturn(
                200,
                '',
                $this->WithdrawalService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(400, 'Withdrawal method request failed');
        }
    }
}

