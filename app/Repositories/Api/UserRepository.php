<?php


namespace App\Repositories\Api;


use App\Libs\Aes;
use App\Models\Cx_Banks;
use App\Models\Cx_Charge_Logs;
use App\Models\Cx_System;
use App\Models\Cx_User;
use App\Models\Cx_User_Balance_Logs;
use App\Models\Cx_User_Bank;
use App\Models\Cx_User_Commission_Logs;
use App\Mongodb;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class UserRepository
{
    protected $Cx_User, $Cx_System, $Cx_Charge_Logs, $Cx_User_Balance_Logs, $Cx_Banks;
    private $cx_User_Bank;
    private $cx_User_Commission_Logs;

    public $_data = [];

    public function __construct(
        Cx_User $cx_User,
        Cx_System $cx_System,
        Cx_Charge_Logs $charge_Logs,
        Cx_User_Balance_Logs $Cx_User_Balance_Logs,
        Cx_User_Bank $cx_User_Bank,
        Cx_User_Commission_Logs $cx_User_Commission_Logs,
        Cx_Banks $cx_Banks
    )
    {
        $this->Cx_User = $cx_User;
        $this->Cx_System = $cx_System;
        $this->Cx_Charge_Logs = $charge_Logs;
        $this->Cx_User_Balance_Logs = $Cx_User_Balance_Logs;

        $this->cx_User_Bank = $cx_User_Bank;
        $this->cx_User_Commission_Logs = $cx_User_Commission_Logs;
        $this->Cx_Banks = $cx_Banks;
    }

    public function getcode()
    {
        $code = $this->CreateCode();
        //把接收的邀请码再次返回给模型
        if ($this->recode($code)) {
            //不重复 返回验证码
            return $code;
        } else {
            //重复 再次生成
            while (true) {
                $this->getcode();
            }
        }
    }

    public function findByPhone($phone)
    {
        return $this->Cx_User->where("phone", $phone)->first();
    }

    public function CreateCode()
    {
        $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $rand = $code[rand(0, 25)]
            . strtoupper(dechex(date('m')))
            . date('d') . substr(time(), -5)
            . substr(microtime(), 2, 5)
            . sprintf('%02d', rand(0, 99));
        for (
            $a = md5($rand, true),
            $s = '0123456789ABCDEFGHIJKLMNOPQRSTUV',
            $d = '',
            $f = 0;
            $f < 6;
            $g = ord($a[$f]),
            $d .= $s[($g ^ ord($a[$f + 8])) - $g & 0x1F],
            $f++
        ) ;
        return $d;
    }

    public function recode($code)
    {
        $count = $this->Cx_User->where("code", $code)->count();
        if ($count > 0) {
            return false;
        }
        return true;
    }

    //得到代理上级关系
    public function findAgentByCode($code)
    {
        $arr["one_id"] = null;
        $arr["two_id"] = null;
        $data = $this->Cx_User->where("code", $code)->first();
        if (isset($data->id)) {
            if (!empty($data->two_recommend_id) && empty($data->one_recommend_id)) {
//                $arr["one_id"] = $data->two_recommend_id;  //
//                $arr["two_id"] = $data->id;
                $arr["one_id"] = $data->id;  //
                $arr["two_id"] = $data->two_recommend_id;
            } elseif (!empty($data->one_recommend_id) && empty($data->two_recommend_id)) {
                $arr["one_id"] = $data->one_recommend_id;
                $arr["two_id"] = $data->id;
            } elseif (!empty($data->one_recommend_id) && !empty($data->two_recommend_id)) {
//                $arr["one_id"] = $data->two_recommend_id;
//                $arr["two_id"] = $data->id;
                $arr["one_id"] = $data->id;
                $arr["two_id"] = $data->two_recommend_id;
            } else {
                $arr["one_id"] = $data->id;
                $arr["two_id"] = null;
            }
        }
        return ["user" => $data, "agent" => $arr];
    }

    /**
     * 只更新用户成功充值余额
     */
    public function updateRechargeBalance(object $user, $money)
    {
        $user->is_first_recharge = (int)$user->is_first_recharge + 1;  // 累计充值次数
        $user->total_recharge = bcadd($user->total_recharge, $money, 2);  // 累计充值金额
        return $this->updateBalance($user, $money, 2, "充值成功，充值金额{$money}");
    }

    /**
     * 只更新用户成功充值余额
     */
    public function updateRechargeBalance2(object $user, $money)
    {
        $user->is_first_recharge = (int)$user->is_first_recharge + 1;  // 累计充值次数
        $user->total_recharge = bcadd($user->total_recharge, $money, 2);  // 累计充值金额
        return $this->updateBalance($user, $money, 16, "银行卡直接充值余额，充值金额{$money}");
    }

    public function rebate($rechargeLog, $config, $money)
    {
        if(!$config)return;
        if(!isset($config['status']) || !isset($config['percent']) || !isset($config['max_rebate']) || !isset($config['min_recharge']))return;
        if($config['status'] != 1)return;
        if($config['percent'] <= 0)return;
        if($config['min_recharge'] > $money)return;
        $rebate = bcmul($money, $config['percent'],2);
        if($config['max_rebate'] > 0)$rebate = min($rebate, $config['max_rebate']);
        if($rebate <= 0)return;
        $user = $this->findByIdUser($rechargeLog->user_id);
        $this->updateBalance($user, $rebate, 14,"充值{$money},返利比例{$config['percent']}");
    }

    /**
     * 用户余额变动记录
     */
    public function updateBalance(object $user, $money, $type, $msg)
    {
        $dq_balance = $user->balance;    // 当前余额
        $wc_balance = bcadd($dq_balance, $money, 2);   // 变动后余额

        $user->balance = $wc_balance;
        $user->save();

        // 余额变动记录
        $data = [
            "user_id" => $user->id,
            "type" => $type,
            "dq_balance" => $dq_balance,
            "wc_balance" => $wc_balance,
            "time" => time(),
            "msg" => $msg,
            "money" => abs($money),
            "is_first_recharge" => $user->is_first_recharge == 1 ? 1 : 0,
        ];
        return $this->Cx_User_Balance_Logs->insert($data);
    }

    /**
     * $user_id 用户id
     * $money 变动金额
     * $type 场景 1.下注 2.充值 3.提现 4.签到礼金 5.红包礼金 6.投注获胜 7.签到零回扣 8.后台赠送礼金 9.手动上分 10.手动下分 11.提现驳回
     * $msg 描述
     * $dq_balance 当前金额
     * $wc_balance 操作后金额
     * 只记录用户余额变动记录
     */
    public function addBalanceLog($user_id, $money, $type, $msg, $dq_balance, $wc_balance)
    {
        // 余额变动记录
        $data = [
            "user_id" => $user_id,
            "type" => $type,
            "dq_balance" => $dq_balance,
            "wc_balance" => $wc_balance,
            "time" => time(),
            "msg" => $msg,
            "money" => abs($money),
//            "is_first_recharge" => $user->is_first_recharge == 1 ? 1 : 0,
        ];
        return $this->Cx_User_Balance_Logs->insert($data);
    }

    public function addBalanceLogGetId($user_id, $money, $type, $msg, $dq_balance, $wc_balance)
    {
        // 余额变动记录
        $data = [
            "user_id" => $user_id,
            "type" => $type,
            "dq_balance" => $dq_balance,
            "wc_balance" => $wc_balance,
            "time" => time(),
            "msg" => $msg,
            "money" => abs($money),
//            "is_first_recharge" => $user->is_first_recharge == 1 ? 1 : 0,
        ];
        return $this->Cx_User_Balance_Logs->insertGetId($data);
    }

    /**
     *  佣金余额变动记录
     */
    public function addCommissionLogs(object $user,$money,$dq_commission,$wc_commission,$order_no) {
        $data = [
            "user_id" => $user->id,
            "dq_commission" => $dq_commission,
            "wc_commission" => $wc_commission,
            "time" => time(),
            "order_no" => $order_no,
            "phone" => $user->phone,
            "nickname" => $user->nickname,
            "money" => $money,
            "message" => $user->nickname . "提现佣金" . $money . "成功！"
        ];
        return $this->cx_User_Commission_Logs->insertGetId($data);
    }

    /**
     * 修改用户信息
     * @param $user_id
     * @param $data
     * @return bool|mixed|null
     */
    public function updateUser($user_id, $data)
    {
        $objUser = $this->findByIdUser($user_id);
        foreach ($objUser->toArray() as $field => $value) {
            $objUser->$field = $data[$field] ?? $value;
        }
        if (!$objUser->save()) {
            return false;
        }
        $aes = new Aes();
        $objUser->ping = $aes->encrypt($objUser->id);
        return $objUser;
    }

    /**
     * 创建用户
     * @param $data
     * @return bool|mixed|null
     */
    public function createUser($data)
    {
        $this->Cx_User->fill($data);
        $this->Cx_User->save();
        $this->cacheUser($this->Cx_User->id);
        return  $this->Cx_User->id;
    }


    /**
     * 根据用户名查找用户
     * @param $username
     * @return mixed
     */
    public function getUser($phone)
    {
        return $this->Cx_User->where("phone", $phone)->first();
    }


    /**
     * 根据用户ID查询用户
     * @param $userIds
     * @return mixed
     */
    public function getUsersByIds($userIds)
    {
        return $this->Cx_User->whereIn('id', $userIds)->get(['id', 'nickname', 'head_image']);;
    }


    /**
     * 更新 token
     * @param $user_id
     * @param $token
     */
    public function setToken($user_id, $token)
    {
        $this->Cx_User->where('id', $user_id)->update(['token' => $token]);
    }

    /**
     * 更新用户状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function updateStatus($id, $status)
    {
        return $this->Cx_User->where("id", $id)->update(["status" => $status]);
    }

    /**
     * 根据用户ID查询用户详情
     * @param $user_id
     * @return mixed
     */
    public function findByIdUser($user_id)
    {
        return $this->Cx_User->find($user_id);
    }

    /**
     * 根据用户ID查询用户详情 | 使用悲观锁
     * @param $user_id
     * @return mixed
     */
    public function findUserByIdLock($user_id)
    {
        return $this->Cx_User->where('id', '=', $user_id)->lockForUpdate()->first();
    }

    /**
     * 根据用户ID查询用户详情限制字段
     * @param $user_id
     * @return mixed
     */
    public function selectByUserId($user_id, $select)
    {
        return $this->Cx_User->select($select)->find($user_id);
    }

    /**
     * 修改代理收益
     * @param $data
     * @return mixed
     */
    public function updateAgentMoney($data)
    {
        return $this->Cx_User->where("id", $data["id"])->update($data);
    }

    public function updateAgentMoneyRegister($data)
    {
        return $this->Cx_User->where("id", $data['id'])->update($data);
    }

    /**
     * 查询系统原本收入
     * @return mixed
     */
    public function findSystemCharge()
    {
        return $this->Cx_System->where("id", 1)->first();
    }

    public function updateSystemCharge($charge)
    {
        return $this->Cx_System->where("id", 1)->update(["platform_charge" => $charge]);
    }

    public function addChargeLogs($data)
    {
        return $this->Cx_Charge_Logs->insertGetId($data);
    }

    /**
     * 查找用户是否存在
     * @param $username
     * @return mixed
     */
    public function Count($phone)
    {
        return $this->Cx_User->where("phone", $phone)->count();
    }

    /**
     * 查找ip是否存在
     * @param $ip
     * @return mixed
     */
    public function ipExist($ip)
    {
        return $this->Cx_User->where("ip", $ip)->count();
    }

    /**
     * @param $phone
     * @param $pwd
     * @return array
     */
    public function UpPwd($phone, $pwd)
    {
        if ($this->Cx_User->where("phone", $phone)->count() < 1) {
            return [
                "code" => 401,
                "msg" => "The user does not exist, please check whether the phone number is entered correctly",
                "data" => null
            ];
        } else {
            if ($this->Cx_User->where("phone", $phone)->update(["password" => $pwd])) {
                return [
                    "code" => 200,
                    "msg" => "Password reset successfully",
                    "data" => null
                ];
            } else {
                return [
                    "code" => 402,
                    "msg" => "Password reset failed, please contact customer service",
                    "data" => null
                ];
            }
        }
    }



    // cache 相关

    /**
     * 缓存用户信息
     * @param $id
     * @return mixed|null
     */
    public function cacheUser($user_id)
    {
        if (!$user_id || $user_id <= 0 || !is_numeric($user_id)) {
            return null;
        } // if $id is not a reasonable integer, return false instead of checking users table

        return Cache::rememberForever(Cx_User::CACHE_USER_PROFILE . $user_id, function () use ($user_id) {
            return $this->findByIdUser($user_id);
        });
    }

    /**
     * 更新缓存用户信息
     * @param $user_id    用户ID
     * @param $user_obj   用户对象
     * @return mixed
     */
    public function updateCacheUser($user_id, $user_obj)
    {
        return Cache::put(Cx_User::CACHE_USER_PROFILE . $user_id, $user_obj);
    }

    /**
     * 删除缓存用户信息
     * @param $user_id    用户ID
     * @param $user_obj   用户对象
     * @return mixed
     */
    public function deleteCacheUser($user_id)
    {
        return Cache::forget(Cx_User::CACHE_USER_PROFILE . $user_id);
    }


    /*
     * 用户提现
     *  @params $userId  用户ID
     *  @params $userId  提现金额
     */
    public function Withdrawal($user_id, $bank_id, $money, $user_data)
    {
        $u_money = $user_data->balance - $money;
        DB::beginTransaction();
        try {
            $this->Cx_User->where("id", $user_id)->update(['balance' => $u_money, 'd_balance' => $money]);

            $arr["user_id"] = $user_id;
            $arr["bank_id"] = $bank_id;
            $arr["money"] = $money;
            $arr["create_time"] = time();
            $this->Cx_Withdrawal_Record->insert($arr);

            DB::commit();
            $connection = Mongodb::connectionMongodb('cx_user_balance_logs');
            $connection->insert(array("user_id" => $user_id, "type" => 5, "dq_balance" => $user_data->balance, "wc_balance" => $u_money, "time" => time(), "msg" => "提现余额扣除到冻结金额" . $money));

            $user_obj = $this->Cx_User->where('id', $user_id)->first();
            $this->updateCacheUser($user_id, $user_obj);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    //检查用户是否拥有改张银行卡
    public function isBank($user_id, $bank_id)
    {
        return $this->cx_User_Bank->where("id", $bank_id)->where('user_id', $user_id)->count();
    }

    /**
     * 根据银行卡ID查找我的银行卡
     */
    public function getBankByBankId($bank_id)
    {
        return $this->cx_User_Bank->find($bank_id);
    }

    public function Withdrawal_List($limit, $offset, $user_id)
    {
        return $this->Cx_Withdrawal_Record->with(array(
            'bank_name' => function ($query) {
                $query->select('id', 'account_holder');
            },
        ))->where("user_id", $user_id)->offset($offset)->limit($limit)->get()->toArray();
    }

    public function findNewUser()
    {
        return $this->Cx_User->where("new_old", 0)->get()->toArray();
    }

    public function updateNewOrOld($id)
    {
        return $this->Cx_User->where("id", $id)->update(["new_old" => 1]);
    }

    public function balance($id)
    {
        return $this->Cx_User->where("id", $id)->value("balance");
    }

    public function buyProduct($user, $price, $back_money)
    {
        $dq_balance = $user->balance;
        $wc_balance = bcadd($dq_balance, $back_money,2);
        ##更新金额
        $user->point = bcsub($user->point, $price,2);
        $user->balance = $wc_balance;
        $res = $user->save();
        if(!$res)return false;
        ##增加余额变化记录
        $log_id = $this->addBalanceLogGetId($user->id, $back_money,13,"打码量兑换余额{$back_money}", $dq_balance, $wc_balance);
        return $log_id;
    }

    public function registerRebate($user, $config)
    {
        if(!$config)return;
        if($config['status'] != 1 || $config['rebate'] <= 0)return;
        if(isset($config['is_leader_limit']) && $config['is_leader_limit']){ ##限制指定组长直邀的用户才返利
            if(!$user->invite_relation)return;
            $relation = trim($user->invite_relation,'-');
            $relation = explode('-',$relation);
            if(!$relation[0])return;
            $leader_id = $relation[count($relation)-1];
            $invite_user = $this->findByIdUser($leader_id);
            if(!$invite_user)return;
            if($invite_user->is_group_leader != 1 || !$invite_user->is_recommend_rebate)return;
        }
        $this->updateBalance($user, $config['rebate'], 15,"注册赠送彩金");
    }

    public function bankList($where)
    {
        return makeModel($where, $this->Cx_Banks)
            ->select(['bank_name as label', 'busi_code as value'])
            ->get();
    }

    public function getUserWhatsApp($user_id)
    {
        return $this->Cx_User->where('id', '=', $user_id)->where('is_customer_service', '=', 1)->select(['id', 'whats_app_account', 'whats_app_link'])->first();
    }

    public function addUserBetting($user_id, $betting)
    {
        return $this->Cx_User->where('id', '=', $user_id)->update(
            [
                'cl_betting' => DB::raw("cl_betting + {$betting}"),
                'point' => DB::raw("point + {$betting}"),
            ]
        );
    }
}
