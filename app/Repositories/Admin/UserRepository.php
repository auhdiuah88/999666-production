<?php


namespace App\Repositories\Admin;


use App\Models\Cx_User;
use App\Models\Cx_User_Balance_Logs;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class UserRepository extends BaseRepository
{
    private $Cx_User, $Cx_User_Balance_Logs;

    public function __construct(Cx_User $cx_User, Cx_User_Balance_Logs $balance_Logs)
    {
        $this->Cx_User = $cx_User;
        $this->Cx_User_Balance_Logs = $balance_Logs;
    }

    public function findAll($offset, $limit)
    {
        return $this->Cx_User->where("is_customer_service", 0)->orderByDesc("last_time")->offset($offset)->limit($limit)->select(["*", "id as total_win_money"])->get()->setAppends(['online_status'])->toArray();
    }

    public function getBalanceLogs($userId, $offset, $limit, $type)
    {
        $where = [
            'user_id' => ['=', $userId]
        ];
        if($type > 0)$where['type'] = ['=', $type];
        return makeModel($where, $this->Cx_User_Balance_Logs)->with(["admin" => function ($query) {
            $query->select(["id", "nickname"]);
        }])->offset($offset)->limit($limit)->orderByDesc("time")->select(["*", "type as type_map"])->get()->toArray();
    }

    public function countBalanceLogs($userId, $type)
    {
        $where = [
            'user_id' => ['=', $userId]
        ];
        if($type > 0)$where['type'] = ['=', $type];
        return makeModel($where, $this->Cx_User_Balance_Logs)->count("id");
    }

    public function getRecommenders()
    {
        return $this->Cx_User->orderByDesc("last_time")->select(["id", "nickname"])->get()->toArray();
    }

    public function countAll()
    {
        return $this->Cx_User->where("is_customer_service", 0)->count("id");
    }

    public function findById($id)
    {
        return $this->Cx_User->where("id", $id)->first();
    }

    public function findByIds($ids)
    {
        return makeModel($this->Cx_User, ['id', 'in', $ids])->get()->toArray();
    }

    public function findByPhone($phone)
    {
        return $this->Cx_User->where("phone", $phone)->select(['id', 'nickname', 'phone'])->first();
    }

    public function findCustomerServiceByPhone($phone)
    {
        return $this->Cx_User->where("phone", $phone)->where("is_customer_service", 1)->select(['id', 'nickname', 'phone'])->first();
    }

    public function getUserByConditions($data, $offset, $limit)
    {
        return $this->whereCondition($data, $this->Cx_User)->offset($offset)->limit($limit)->orderByDesc("last_time")->select(['*', 'id as total_win_money'])->get()->setAppends(['online_status'])->toArray();
    }

    public function countUserByConditions($data)
    {
        return $this->whereCondition($data, $this->Cx_User)->count("id");
    }

    public function addUser($data)
    {
        return $this->Cx_User->insertGetId($data);
    }

    public function editUser($data)
    {
        return $this->Cx_User->where("id", $data["id"])->update($data);
    }

    public function delUser($id)
    {
        return $this->Cx_User->where("id", $id)->delete();
    }

    public function batchModifyRemarks(array $ids, string $message)
    {
        return $this->Cx_User->whereIn("id", $ids)->update(["remarks" => $message]);
    }

    public function modifyUserStatus($id, $status)
    {
        return $this->Cx_User->where("id", $id)->update(["status" => $status]);
    }

    public function getCustomerService()
    {
        return $this->Cx_User->where("is_customer_service", 1)->select(["id", "phone"])->get()->toArray();
    }

    public function modifyCustomerService($ids, $customer_id)
    {
        return $this->Cx_User->whereIn("id", $ids)->update(["customer_service_id" => $customer_id]);
    }

    public function modifyEmptyAgent($ids, $data)
    {
        return $this->Cx_User->whereIn("id", $ids)->update($data);
    }

    public function addLogs($data)
    {
        return $this->Cx_User_Balance_Logs->insertGetId($data);
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
        $admin_id = request()->get('admin_id');
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
            'admin_id' => $admin_id
        ];
        return $this->Cx_User_Balance_Logs->insert($data);
    }

    /**
     * 修改用户假流水
     * @param $user_id
     * @param $money
     * @return mixed
     */
    public function editFakeBettingMoney($user_id, $money)
    {
        return $this->Cx_User->where('id', $user_id)->update(['fake_betting_money' => $money]);
    }
    public function findGroupLeaders($where, $offset, $limit)
    {
        return $this->Cx_User
            ->groupLeader()
            ->where($where)
            ->with(
                [
                    'admin' => function($query){
                        $query->select(['id', 'user_id', 'username']);
                    }
                ]
            )
            ->offset($offset)
            ->limit($limit)
            ->orderByDesc("last_time")
            ->get()
            ->toArray();
    }

    public function countGroupLeaders($where)
    {
        return $this->Cx_User->groupLeader()->where($where)->count();
    }

    public function logicDel($id)
    {
        return $this->Cx_User->where('id',$id)->update(['deleted_at' => time()]);
    }

    public function searchAccount($phone)
    {
        return $this->Cx_User->where([
            ['phone', $phone],
            ['is_group_leader',  2],
            ['is_customer_service', 1]
        ])->select(['id', 'nickname', 'phone'])->first();
    }

    public function clearFakeBetting()
    {
        return $this->Cx_User->where("fake_betting_money", "<>", "0")->update(['fake_betting_money'=>0]);
    }

    public function searchUser($where)
    {
         return makeModel($where, $this->Cx_User)->select(['id', 'phone'])->get();
    }

    public function updateOneRecomByTwo($data)
    {
        return $this->Cx_User->where("two_recommend_id", "=", $data['id'])->update(['one_recommend_id'=>$data['two_recommend_id'], 'one_recommend_phone'=>$data['two_recommend_phone']]);
    }

    public function countOneRecomBumByTwo($data)
    {
        return $this->Cx_User->where("two_recommend_id", "=", $data['id'])->count();
    }

    public function subOneNumber($id, $num)
    {
        return $this->Cx_User->where("id", "=", $id)->decrement('one_number', $num);
    }

    public function addOneNumber($id, $num)
    {
        return $this->Cx_User->where("id", "=", $id)->increment('one_number', $num);
    }

    public function subTwoNumber($id, $num)
    {
        return $this->Cx_User->where("id", "=", $id)->decrement('two_number', $num);
    }

    public function addTwoNumber($id, $num)
    {
        return $this->Cx_User->where("id", "=", $id)->increment('two_number', $num);
    }

    public function updateInviteRelation($data)
    {
        $users = $this->Cx_User->where("invite_relation", "like", "%-{$data['id']}-%")->select(['id', 'invite_relation'])->get();
        if($users->isEmpty())return true;
        $users = $users->toArray();
        foreach($users as $user){
            if($user['invite_relation']){
                $relation_arr = explode((string)($data['id']), $user['invite_relation']);
                $new_relation = trim(trim($relation_arr[0],'-') . '-' . $data['id'] . '-' . trim($data['invite_relation'],'-'),'-');
                $new_relation = $new_relation?'-' . $new_relation . '-' : "";
            }else{
                $new_relation = '-' . trim($data['id'] . '-' . trim($data['invite_relation'],'-'),'-') . '-';
            }
            $res = $this->Cx_User->where("id", "=", $user['id'])->update(['invite_relation'=>$new_relation]);
            if($res === false)return false;
        }
        return true;
    }

    public function groupUpList($where, $size, $relation)
    {
        $relation = implode(',',$relation);
        return makeModel($where, $this->Cx_User)
            ->select(['id', 'phone', 'balance', 'cl_withdrawal', 'commission', 'total_recharge', 'cl_betting', 'cl_betting_total', 'is_group_leader', 'is_customer_service'])
            ->orderByRaw("FIELD(id, " . $relation . ")")
            ->paginate($size);
    }

    public function groupDownList($where, $size)
    {
        return makeModel($where, $this->Cx_User)
            ->select(['id', 'phone', 'balance', 'cl_withdrawal', 'commission', 'total_recharge', 'cl_betting', 'cl_betting_total', 'is_group_leader', 'is_customer_service'])
            ->orderBy('id', 'asc')
            ->paginate($size);
    }

    public function exportUserList($where, $size, $order)
    {
//        return makeModel($where, $this->Cx_User)
//            ->select(['id', 'phone', 'balance', 'cl_withdrawal', 'reg_time', 'total_recharge', 'cl_betting', 'status'])
//            ->orderBy
    }

}
