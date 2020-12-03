<?php


namespace App\Repositories\Api;


use App\Models\Cx_Charge_Logs;
use App\Models\Cx_System;
use App\Models\Cx_User;
use App\Models\Cx_User_Balance_Logs;
use App\Models\Cx_User_Bank;
use App\Mongodb;
use Illuminate\Support\Facades\Cache;


class UserRepository
{
    protected $Cx_User, $Cx_System, $Cx_Charge_Logs, $Cx_User_Balance_Logs;
    private $cx_User_Bank;

    public $_data = [];

    public function __construct(Cx_User $cx_User,
                                Cx_System $cx_System,
                                Cx_Charge_Logs $charge_Logs,
                                Cx_User_Balance_Logs $Cx_User_Balance_Logs,
                                Cx_User_Bank $cx_User_Bank
    )
    {
        $this->Cx_User = $cx_User;
        $this->Cx_System = $cx_System;
        $this->Cx_Charge_Logs = $charge_Logs;
        $this->Cx_User_Balance_Logs = $Cx_User_Balance_Logs;

        $this->cx_User_Bank = $cx_User_Bank;
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
     * 修改用户信息
     * @param $user_id
     * @param $data
     * @return bool|mixed|null
     */
    public function updateUser($user_id, $data)
    {
        $objUser = $this->cacheUser($user_id);
        foreach ($objUser->toArray() as $field => $value) {
            $objUser->$field = $data[$field] ?? $value;
        }
        if (!$objUser->save()) {
            return false;
        }
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
        return $this->cacheUser($this->Cx_User->id);
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
     * 修改代理收益
     * @param $data
     * @return mixed
     */
    public function updateAgentMoney($data)
    {
        return $this->Cx_User->where("id", $data["id"])->update($data);
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
     * @param $phone
     * @param $pwd
     * @return array
     */
    public function UpPwd($phone, $pwd)
    {
        if ($this->Cx_User->where("phone", $phone)->count() < 1) {
            return $data = array("code" => 401,
                "msg" => "该用户不存在请检查手机号是否输入正确",
                "data" => null);
        } else {
            if ($this->Cx_User->where("phone", $phone)->update(["password" => $pwd])) {
                return $data = array("code" => 200,
                    "msg" => "重置密码成功",
                    "data" => null);
            } else {
                return $data = array("code" => 402,
                    "msg" => "重置密码失败，请联系客服",
                    "data" => null);
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
}
