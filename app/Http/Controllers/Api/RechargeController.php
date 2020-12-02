<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\RechargeService;
use App\Services\Api\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class RechargeController extends Controller
{
    protected $UserService, $rechargeService;


    public function __construct(UserService $userService, RechargeService $rechargeService)
    {
        $this->UserService = $userService;
        $this->rechargeService = $rechargeService;
    }

    /**
     * 用户充值-请求充值订单-二维码 （充值界面提交）
     */
    public function recharge(Request $request)
    {
        $rules = [
            "money" => "required|integer",
            "pay_type" => "required",       // 充值方式
        ];
        $validator = Validator::make($request->post(), $rules);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        if (!$result = $this->rechargeService->rechargeOrder($request)) {
            return $this->AppReturn(400, $this->rechargeService->_msg, new \StdClass());
        }
        return $this->AppReturn(200, '用户充值-充值订单二维码', $result);
    }

    /**
     * 充值记录
     */
    public function rechargeLog(Request $request)
    {
        $rules = [
            "status" => "required|integer|in:1,2,3",
            "page" => "required|integer|min:1",
            "limit" => "required|integer",
        ];
        $validator = Validator::make($request->post(), $rules);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        return $this->AppReturn(200, '充值记录:', $this->rechargeService->rechargeLog($request));
    }

    /**
     * 充值回调接口
     */
    public function rechargeCallback(Request $request)
    {
//        Log::channel('mytest')->info('rechargeCallback', $request->all());

        if ($this->rechargeService->rechargeCallback($request)) {
            return 'success';
        }
        return 'fail';
    }
}
