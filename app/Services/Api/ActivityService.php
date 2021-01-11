<?php


namespace App\Services\Api;


use App\Common\Common;
use App\Repositories\Api\ActivityRepository;
use App\Repositories\Api\RechargeRepository;
use App\Repositories\Api\UserRepository;
use App\Services\Library\Auth;
use App\Services\Library\Netease\IM;
use App\Services\Library\Netease\SMS;
use App\Services\Library\Upload;
use http\Params;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ActivityService extends BaseService
{
    private $activityRepository;
    private $userRepository;
    private $rechargeRepository;


    public function __construct(ActivityRepository $activityRepository,
                                UserRepository $userRepository,
                                RechargeRepository $rechargeRepository
    )
    {
        $this->activityRepository = $activityRepository;
        $this->userRepository = $userRepository;
        $this->rechargeRepository = $rechargeRepository;
    }

    /**
     * 任务列表
     */
    public function getTaskList($request)
    {
        $token = $request->header("token");
        $token = urldecode($token);
        list($user_id, $t) = explode("+", Crypt::decrypt($token));

        $user = $this->userRepository->findByIdUser($user_id);
        $getedTaskIds = $this->activityRepository->getUserGetedTaskIds($user_id);

        $task_list = $this->activityRepository->getAllTask();
        $done_count = $user->rec_ok_count;   // 推荐成功充值人数

        $task_list->each(function ($item, $key) use (&$done_count, $getedTaskIds) {
            $item->is_done = 0;     // 是否完成此任务
            $item->doing_num = 0;   // 当前任务完成数
            $item->is_geted = 0;    // 是否领取
            if ($done_count >= $item->value) {
                $item->is_done = 1;
                $done_count = $done_count - $item->value;
                $item->doing_num = $item->value;
            } else {
                $item->doing_num = $done_count;
            }

            if ($getedTaskIds->intersect($item->id)->isNotEmpty()) {
                $item->is_geted = 1;
            }
        });

        return $task_list;
    }

    /**
     * 获取任务奖励
     */
    public function taskRewardGet($request, array $data)
    {
        $token = $request->header("token");
        $token = urldecode($token);
        list($user_id, $t) = explode("+", Crypt::decrypt($token));
        $user = $this->userRepository->findByIdUser($user_id);

        $task_id = $data['task_id'];
        $task = $this->activityRepository->getTaskByID($task_id);

        if ($this->activityRepository->isTaskUserGeted($task, $user)) {
            $this->_msg = 'You have already claimed the reward';
            return false;
        }

        $preTaskSum = $this->activityRepository->getPreTaskSum($task->value);
        if ($preTaskSum > $user->rec_ok_count) {
            $this->_msg = 'Please complete the previous task first';
            return false;
        }

        DB::beginTransaction();
        try {
            $money = (int)$task->reward + (int)$task->add_reward;
            $this->userRepository->updateBalance($user, $money, 5, 'Successfully withdraw red envelope gift money，Award amount ' . $money);
            $this->activityRepository->recordTaskUserGeted($task, $user);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->_msg = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * 我的签到包信息
     */
    public function signInfo($request)
    {
        $token = $request->header("token");
        $token = urldecode($token);
        list($user_id, $t) = explode("+", Crypt::decrypt($token));

        $count = $this->activityRepository->totalSignPackageCount($user_id);
        $info = [
            'packageCount' => $count,
//            'receiveAmount' => $this->activityRepository->totalSignPackageReceiveAmount($user_id),
            'totalAmount' => $this->activityRepository->totalSignPackageAmount($user_id),
            'rewardAmount' => $this->activityRepository->totalSignPackageRewardAmount($user_id),
            'buy' => $count ? true : false
        ];
        return $info;
    }

    /**
     *  每日签到包列表
     */
    public function getAllSignInGetMoney($request)
    {
        $token = $request->header("token");
        $token = urldecode($token);
        list($user_id, $t) = explode("+", Crypt::decrypt($token));
//        $user = $this->userRepository->findByIdUser($user_id);
        $list = $this->activityRepository->getAllSignInGetMoney();
        $signOrderProductIds = $this->activityRepository->getUserSignOrderProductIds($user_id);
        $list->each(function ($item, $key) use ($signOrderProductIds) {    // button_type:1=可购买,2=已经购买,3=已卖光
            $item->button_type = 1;
            if ($signOrderProductIds->intersect($item->id)->isNotEmpty()) {
                $item->button_type = 2;
            } elseif ($item->stock == 0) {
                $item->button_type = 3;
            }
        });
        return $list;
    }

    /**
     * 购买每日签到回扣包
     */
    public function buySignInGetMoney($request)
    {
        $token = $request->header("token");
        $token = urldecode($token);
        list($user_id, $t) = explode("+", Crypt::decrypt($token));
        $user = $this->userRepository->findByIdUser($user_id);

        $product_id = $request->post('product_id');
        $signProduct = $this->activityRepository->findSignProductById($product_id);

        if ($signProduct->amount > $user->balance) {
            $this->_msg = 'Insufficient balance, please recharge';
            return false;
        }

        $signOrder = $this->activityRepository->getValidSignOrder($product_id, $user_id);
        if ($signOrder) {
            $this->_msg = 'This product has been purchased';
            return false;
        }

        if ($signProduct->stock == 0) {
            $this->_msg = 'This product has sold out';
            return false;
        }

        // 这里应该跳转到支付

        DB::beginTransaction();
        try {

            $this->activityRepository->createSignOrder($signProduct, $user);
            $signProduct->stock = $signProduct->stock - 1;
            $signProduct->save();

            // 余额变动记录
            $money = $signProduct->amount;
            $this->userRepository->updateBalance($user, -$money, 4, '购买签到礼金包，扣除'.$money);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->_msg = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * 点击每日签到；领取回扣
     */
    public function doGetMoney($request)
    {
        $token = $request->header("token");
        $token = urldecode($token);
        list($user_id, $t) = explode("+", Crypt::decrypt($token));

        $product_id = $request->post('product_id');

        $signProduct = $this->activityRepository->findSignProductById($product_id);

        $signOrder = $this->activityRepository->getValidSignOrder($product_id, $user_id);
        if (!$signOrder) {
            $this->_msg = 'Have not purchased this product';
            return false;
        }

        $lastSignOrderOp = $this->activityRepository->getLastSignOrderOp($product_id, $user_id, $signOrder->id);
        if ($lastSignOrderOp) {
            if (date("Y-m-d", $lastSignOrderOp->sign_time) == date("Y-m-d", time())) {
                $this->_msg = 'Please come back tomorrow';
                return false;
            }
        }

        DB::beginTransaction();
        try {
            $money = $signProduct->daily_rebate;
            $user = $this->userRepository->findByIdUser($user_id);
            $this->activityRepository->createSignOrderOp($signProduct, $user_id, $signOrder->id);
            $this->userRepository->updateBalance($user, $money, 7, '签到领取');

            $signOrder->yet_receive_count =  $signOrder->yet_receive_count+1;
            $signOrder->yet_receive_amount = bcadd($signOrder->yet_receive_amount,$money,2);
            $signOrder->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->_msg = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * 最近的其他用户领取回扣记录
     */
    public function getPackageReceiveInfo()
    {
        $list = $this->activityRepository->getRecentSignOrderOpList();
        $list->each(function ($item, $key) {
            $item->nickname = $item->user->nickname;
            unset($item->user);
        });
        return $list;
    }
}
