<?php


namespace App\Repositories\Api;

use App\Models\Cx_Direct_Recharge_logs;
use App\Models\Cx_User_Recharge_Log;
use App\Models\Cx_Withdrawal_Record;
use App\Mongodb;
use Illuminate\Support\Facades\DB;

class RechargeRepository
{
    protected $cx_User_Recharge_Log;
    protected $cx_Withdrawal_Record;
    protected $cx_Direct_Recharge_logs;
    public $_data = [];

    public function __construct(
        Cx_User_Recharge_Log $cx_User_Recharge_Log,
        Cx_Withdrawal_Record $cx_Withdrawal_Record,
        Cx_Direct_Recharge_logs $cx_Direct_Recharge_logs
    )
    {
        $this->cx_User_Recharge_Log = $cx_User_Recharge_Log;
        $this->cx_Withdrawal_Record = $cx_Withdrawal_Record;
        $this->cx_Direct_Recharge_logs = $cx_Direct_Recharge_logs;
    }

    /**
     *  添加充值记录
     */
    public function addRechargeLog(object $user, $money, $order_no, $pay_type, $pltf_order_id = '',
                                   $native_url = '', $verify_money = '', $match_code = '', $sign = '')
    {
        $data = [
            'is_first_recharge' => $user->is_first_recharge == 0 ? 1 : 0,
            'user_id' => $user->id,
            'phone' => $user->phone,
            'nickname' => $user->nickname,
            'money' => $money,
            'order_no' => $order_no,
            'status' => 1,
            'time' => time(),
            'dq_balance' => $user->balance,
            'wc_balance' => bcadd($user->balance, $money, 2),
            'pay_company' => '',
            'pay_type' => $pay_type,
//            'msg' => $msg,
            'pltf_order_id' => $pltf_order_id,
            'native_url' => $native_url,
            'verify_money' => $verify_money,
            'match_code' => $match_code,
            'expire' => 600,
            'sign' => $sign,
        ];
        $this->cx_User_Recharge_Log->insert($data);
    }

    /**
     *  获取充值记录
     */
    public function getRechargeLogs($uid, $status = 1, $limit = 10, $page = 1)
    {
        return $this->cx_User_Recharge_Log->where("user_id", $uid)->where('status', $status)->orderBy('time', 'desc')
            ->select('order_no', 'time', 'money', 'status', 'native_url')
            ->paginate($limit, ['*'], 'page', $page)->getCollection();
    }

    /**
     * 根据订单号查询充值记录
     */
    public function findRechargeLogByOrderNo($order_no)
    {
        return $this->cx_User_Recharge_Log->where('order_no', $order_no)->first();
    }

    /**
     * 根据条件查询充值信息
     */
    public function getRechargeInfoByCondition(array $where)
    {
        return $this->cx_User_Recharge_Log->where($where)->first();
    }

    /**
     * 根据条件查询充值信息
     */
    public function getRechargeInfoByConditionLock(array $where)
    {
        return $this->cx_User_Recharge_Log->where($where)->lockForUpdate()->first();
    }

    /**
     * 充值模拟确认
     * @param $order_no
     * @throws \HttpResponseException
     */
    public function rechargeConfirm($order_no)
    {
        $rechargeLog = $this->cx_User_Recharge_Log->where([
            ['order_no', '=', $order_no],
            ['status', '=', 1]
        ])->lockForUpdate()->first();
        if (!$rechargeLog) {
            throw new \HttpResponseException('order err', 414);
        } else {
            $rechargeLog->arrive_money = $rechargeLog->money;
            $rechargeLog->status = 2;
            $rechargeLog->save();
        }
    }

    /**
     * 新增用户直接充值申请上分记录
     * @param $data
     * @return mixed
     */
    public function requestDirectRecharge($data)
    {
        if(empty($data['remark']))unset($data['remark']);
        $data['order_no'] = date('YmdHis') . rand(10000000, 99999999);
        return $this->cx_Direct_Recharge_logs->create($data);
    }

    public function requestDirectRechargeNum($where)
    {
        return makeModel($where, $this->cx_Direct_Recharge_logs)->count();
    }

    public function requestDirectRechargeLogs($where, $size)
    {
        return makeModel($where, $this->cx_Direct_Recharge_logs)
            ->select(['id', 'order_no', 'money', 'real_money', 'created_at', 'remark', 'message', 'status'])
            ->orderByDesc('created_at')
            ->paginate($size);
    }
}
