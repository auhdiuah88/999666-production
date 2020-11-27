<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\ActivityService;
use App\Services\Api\RechargeService;
use App\Services\Api\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ActivityController extends Controller
{
    protected $UserService, $rechargeService, $activityService;


    public function __construct(UserService $userService,
                                RechargeService $rechargeService,
                                ActivityService $activityService

    )
    {
        $this->UserService = $userService;
        $this->rechargeService = $rechargeService;
        $this->activityService = $activityService;
    }

    /**
     * 任务列表
     */
    public function taskList(Request $request)
    {
        $data = $this->activityService->getTaskList($request);
        return $this->AppReturn(200, '任务列表', $data);
    }

    /**
     *  获得任务奖励
     */
    public function taskRewardGet(Request $request)
    {
        $rules = [
            "task_id" => "required",
        ];
        $massages = [
            "phone.required" => "task_id不能为空",
        ];
        $validator = Validator::make($request->post(), $rules, $massages);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }
        if ($this->activityService->taskRewardGet($request, $request->post())) {
            return $this->AppReturn(200, '提取成功');
        }
        return $this->AppReturn(414, $this->activityService->_msg);
    }

    /**
     * 我的签到信息
     */
    public function signInfo(Request $request)
    {
        return $this->AppReturn(200,'我的签到信息',$this->activityService->signInfo($request));
    }

    /**
     * 每日签到获取回扣产品列表
     */
    public function signInGetMoneyList(Request $request)
    {
        return $this->AppReturn(200, '每日签到包列表', $this->activityService->getAllSignInGetMoney($request));
    }

    /**
     * 购买每日签到回扣包
     */
    public function buySignInGetMoney(Request $request)
    {
        $rules = [
            "product_id" => "required",
        ];
        $validator = Validator::make($request->post(), $rules);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }

        if (!$this->activityService->buySignInGetMoney($request)) {
            return $this->AppReturn($this->activityService->_code, $this->activityService->_msg);
        }
        return $this->AppReturn(200, '购买每日签到回扣包成功');
    }

    /**
     * 点击每日签到；领取回扣
     */
    public function doGetMoney(Request $request)
    {
        $rules = [
            "product_id" => "required",
        ];
        $validator = Validator::make($request->post(), $rules);
        if ($validator->fails()) {
            return $this->AppReturn(414, $validator->errors()->first());
        }

        if (!$this->activityService->doGetMoney($request)) {
            return $this->AppReturn($this->activityService->_code, $this->activityService->_msg);
        }
        return $this->AppReturn(200, '领取回扣成功');
    }

    /**
     * 最近的其他用户领取回扣记录
     */
    public function getPackageReceiveInfo()
    {
        return $this->AppReturn(200, "最近的其他用户领取回扣记录", $this->activityService->getPackageReceiveInfo());
    }


}
