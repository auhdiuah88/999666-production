<?php


namespace App\Repositories\Admin;


use App\Dictionary\BalanceTypeDic;
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
        return $this->Cx_User->where("is_customer_service", 0)->orderByDesc("last_time")->offset($offset)->limit($limit)->select(["*", "id as total_win_money"])->get()->setAppends(['online_status', 'group_leader'])->toArray();
    }

    public function getBalanceLogs($userId, $offset, $limit, $type, $timeMap)
    {
        $where = [
            'user_id' => ['=', $userId]
        ];
        if($type)$where['type'] = ['in', $type];
        if($timeMap)$where["time"] = ['BETWEEN', $timeMap];
        return makeModel($where, $this->Cx_User_Balance_Logs)->with(["admin" => function ($query) {
            $query->select(["id", "nickname"]);
        }])->offset($offset)->limit($limit)->orderByDesc("time")->orderByDesc("id")->select(["*", "type as type_map"])->get()->toArray();
    }

    public function countBalanceLogs($userId, $type, $timeMap)
    {
        $where = [
            'user_id' => ['=', $userId]
        ];
        if($type)$where['type'] = ['in', $type];
        if($timeMap)$where["time"] = ['BETWEEN', $timeMap];
        return makeModel($where, $this->Cx_User_Balance_Logs)->count("id");
    }

    public function countInOut($userId, $type, $timeMap)
    {
        $where = [
            'user_id' => ['=', $userId],
        ];
        if($timeMap)$where["time"] = ['BETWEEN', $timeMap];
        {

        }
        $in =  $this->perCount($where, $type,1);
        $out =  $this->perCount($where, $type,2);
        return compact('in','out');
    }

    public function perCount($where, $type, $flag)
    {
        if($type){
            $where['type'] = ['in', array_intersect(BalanceTypeDic::getType($flag), $type)];
        }else{
            $where['type'] = ['in', BalanceTypeDic::getType($flag)];
        }
        return makeModel($where, $this->Cx_User_Balance_Logs)->sum("money");
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
        return $this->whereCondition($data, $this->Cx_User)->offset($offset)->limit($limit)->orderByDesc("last_time")->select(['*', 'id as total_win_money'])->get()->setAppends(['online_status', 'group_leader'])->toArray();
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
     * $user_id ??????id
     * $money ????????????
     * $type ?????? 1.?????? 2.?????? 3.?????? 4.???????????? 5.???????????? 6.???????????? 7.??????????????? 8.?????????????????? 9.???????????? 10.???????????? 11.????????????
     * $msg ??????
     * $dq_balance ????????????
     * $wc_balance ???????????????
     * ?????????????????????????????????
     */
    public function addBalanceLog($user_id, $money, $type, $msg, $dq_balance, $wc_balance)
    {
        $admin_id = request()->get('admin_id');
        // ??????????????????
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
     * ?????????????????????
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

    public function exportUserList($where, $size, $order='id', $direction='desc')
    {
        $data = makeModel($where, $this->Cx_User)
            ->select(['id', 'phone', 'balance', 'cl_withdrawal', 'reg_time', 'total_recharge', 'cl_betting', 'status'])
            ->orderBy($order, $direction)
            ->paginate($size);
        foreach($data as &$item)
        {
            $item->reg_time = date('Y-m-d H:i:s', $item->reg_time);
        }
        return $data;
    }

    public function exportUser($where, $size, $page, $order='id', $direction='desc'): array
    {
         $data = makeModel($where, $this->Cx_User)
            ->select(['id', 'phone', 'balance', 'cl_withdrawal', 'reg_time', 'total_recharge', 'cl_betting', 'status'])
            ->orderBy($order, $direction)
            ->limit($size)
            ->offset(($page-1) * $size)
            ->get($size);
         if($data->isEmpty())return [];
        foreach($data as &$item)
        {
            $item->status = $item->status == 1 ? '??????' : '??????';
            $item->reg_time = date('Y-m-d H:i:s', $item->reg_time);
        }
        return $data->toArray();
    }

    public function getSourceUserIds($reg_source_id=0)
    {
        return $this->Cx_User->where("reg_source_id", $reg_source_id)->pluck('id')->toArray();
    }

    public function getTestUserIds()
    {
        return $this->Cx_User->where("reg_source_id", 1)->pluck('id')->toArray();
    }

    public function getRelUserIds()
    {
        return $this->Cx_User->where("reg_source_id", 0)->pluck('id')->toArray();
    }

}
