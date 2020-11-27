<?php


namespace App\Repositories\Api;


use App\Models\Cx_Sign_Order;
use App\Models\Cx_Sign_Order_Op;
use App\Models\Cx_Sign_Product;
use App\Models\Cx_Task;
use App\Models\Cx_Task_User_Geted;

class ActivityRepository
{
    private $cx_Task;
    private $cx_Task_User_Geted;
    private $sign_Product;
    private $cx_Sign_Order;
    private $cx_Sign_Order_Op;

    public function __construct(Cx_Task $cx_Task,
                                Cx_Task_User_Geted $cx_Task_User_Geted,
                                Cx_Sign_Product $cx_Sign_Product,
                                Cx_Sign_Order $cx_Sign_Order,
                                Cx_Sign_Order_Op $cx_Sign_Order_Op
    )
    {
        $this->cx_Task = $cx_Task;
        $this->cx_Task_User_Geted = $cx_Task_User_Geted;
        $this->sign_Product = $cx_Sign_Product;
        $this->cx_Sign_Order = $cx_Sign_Order;
        $this->cx_Sign_Order_Op = $cx_Sign_Order_Op;
    }

    /**
     *  获取所有任务列表
     */
    public function getAllTask()
    {
        return $this->cx_Task->orderBy('value', 'asc')->get();
    }

    /**
     *  获取用户已经领取过的任务奖励ID
     */
    public function getUserGetedTaskIds($user_id)
    {
        return $this->cx_Task_User_Geted->where('user_id', $user_id)->pluck('task_id');
    }

    /**
     * 通过任务ID获取任务信息
     */
    public function getTaskByID($task_id)
    {
        return $this->cx_Task->find($task_id);
    }

    /**
     * 获取当前任务的前面几个任务总值
     */
    public function getPreTaskSum($value)
    {
        return $this->cx_Task->where('value', '<', $value)->get()->sum(function ($item) {
            return $item->value;
        });
    }

    /**
     * 记录用户提取任务奖励
     */
    public function recordTaskUserGeted(object $task, object $user)
    {
        $data = [
            'reward' => $task->reward,
            'add_reward' => $task->add_reward,
            'geted_time' => time(),
            'user_id' => $user->id,
            'task_id' => $task->id,
        ];
        return $this->cx_Task_User_Geted->insert($data);
    }


    /**
     * 用户是否已经提取任务奖励
     */
    public function isTaskUserGeted(object $task, object $user)
    {
        $where = [
            'task_id' => $task->id,
            'user_id' => $user->id,
        ];
        return (int)$this->cx_Task_User_Geted->where($where)->count();
    }

    /**
     * 用户购买签到包数量
     */
    public function totalSignPackageCount($user_id)
    {
        return $this->cx_Sign_Order->where('user_id', $user_id)->count();
    }

    /**
     * 用户购买签到包总金额
     */
    public function totalSignPackageAmount($user_id)
    {
        return $this->cx_Sign_Order->where('user_id', $user_id)->sum('amount');
    }

    /**
     * 用户签到累计获取金额
     */
    public function totalSignPackageReceiveAmount($user_id)
    {
        return $this->cx_Sign_Order->where('user_id', $user_id)->sum('receive_amount');
    }

    /**
     * 用户签到累计获取金额
     */
    public function totalSignPackageRewardAmount($user_id){
        return $this->cx_Sign_Order_Op->where('user_id', $user_id)->sum('daily_rebate');
    }

    /**
     *  获取所有 每日签到领取钱 列表
     */
    public function getAllSignInGetMoney()
    {
        return $this->sign_Product->orderBy('amount', 'asc')->get();
    }


    public function findSignProductById($product_id)
    {
        return $this->sign_Product->find($product_id);
    }


    /**
     *  获取在有效时间范围内的用户已经购买过的签到产品ID
     */
    public function getUserSignOrderProductIds($user_id)
    {
        $current_time = time();
        return $this->cx_Sign_Order->where('user_id', $user_id)->where('start_time', '<=', $current_time)->where('end_time', '>=', $current_time)->pluck('product_id');
    }

    /**
     *  获取在有效时间范围内的用户购买的每日签到获取回扣的订单;
     */
    public function getValidSignOrder($product_id, $user_id) {
        $current_time = time();
        return $this->cx_Sign_Order->where('user_id', $user_id)->where('product_id', $product_id)->where('start_time', '<=', $current_time)->where('end_time', '>=', $current_time)->first();
    }

    /**
     * 创建购买签到产品订单
     */
    public function createSignOrder(object $signProduct,object $user)
    {
        $end_day = $signProduct->payback_cycle - 1;
        $data = [
            'product_id' => $signProduct->id,
            'amount' => $signProduct->amount,
            'receive_amount' => $signProduct->receive_amount,
            'payback_cycle' => $signProduct->payback_cycle,
            'daily_rebate' => $signProduct->daily_rebate,
            'user_id' => $user->id,
            'phone' => $user->phone,
            'nickname' => $user->nickname,
            'yet_receive_count' => 0,
            'yet_receive_amount' => 0,
            'start_time' => time(),
            'end_time' => strtotime("+" . $end_day . " day")
        ];
        return $this->cx_Sign_Order->insert($data);
    }

    /**
     * 获取最后一条签到记录
     */
    public function getLastSignOrderOp($product_id, $user_id, $order_id)
    {
        return $this->cx_Sign_Order_Op
            ->where('user_id', $user_id)
            ->where('product_id', $product_id)
            ->where('order_id', $order_id)
            ->orderBy('sign_time', 'desc')
            ->first();
    }

    // 创建签到记录
    public function createSignOrderOp(object $signProduct, $user_id, $order_id)
    {
        $data = [
            'product_id' => $signProduct->id,
            'user_id' => $user_id,
            'sign_time' => time(),
            'order_id' => $order_id,
            'daily_rebate' => $signProduct->daily_rebate,
        ];

        return $this->cx_Sign_Order_Op->insert($data);
    }

    // 获取最近的20条领取签到回扣信息
    public function getRecentSignOrderOpList()
    {
        return $this->cx_Sign_Order_Op->orderBy('sign_time', 'desc')->with(['user' => function ($query) {
            $query->select('nickname', 'id');
        }])->limit(20)->get();
    }
}
