<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Admin;
use App\Models\Cx_User;
use App\Repositories\BaseRepository;

class AccountRepository extends BaseRepository
{
    private $Cx_User, $Cx_Admin;

    public function __construct(Cx_User $cx_User, Cx_Admin $cx_Admin)
    {
        $this->Cx_User = $cx_User;
        $this->Cx_Admin = $cx_Admin;
    }

    public function findAll($offset, $limit, $user_id)
    {
        return $this->Cx_User->where("is_customer_service", 1)->where("is_group_leader", 2)->where("invite_relation", "like", "%-{$user_id}-%")->select(["id", "phone", "nickname", "reg_time", "code", "whats_app_account", "whats_app_link", "status"])->offset($offset)->limit($limit)->get()->toArray();
    }

    public function countAll($user_id)
    {
        return $this->Cx_User->where("is_customer_service", 1)->where("is_group_leader", 2)->where("invite_relation", "like", "%-{$user_id}-%")->count("id");
    }

    public function findById($id, $user_id)
    {
        return $this->Cx_User->where("id", $id)->where("is_customer_service", 1)->where("is_group_leader", 2)->where("invite_relation", "like", "%-{$user_id}-%")->select(["id", "phone", "nickname", "reg_time", "code", "whats_app_account", "whats_app_link"])->first();
    }

    public function getCode()
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

    public function addAccount($data)
    {
        return $this->Cx_User->insertGetId($data);
    }

    public function editAccount($data)
    {
        return $this->Cx_User->where("id", $data["id"])->update($data);
    }

    public function delAccount($id)
    {
        return $this->Cx_User->where("id", $id)->delete();
    }

    public function searchAccount($where, $offset, $limit, $user_id)
    {
        return $this->Cx_User->where(function ($query) use ($where) {
            if (array_key_exists("nickname", $where) && $where["nickname"]) {
                $query->where("nickname", "like", "%" . $where["nickname"] . "%");
            }
            if (array_key_exists("phone", $where) && $where["phone"]) {
                $query->where("phone", "like", "%" . $where["phone"] . "%");
            }
            if (array_key_exists("ip", $where) && $where["ip"]) {
                $query->where("ip", "like", "%" . $where["ip"] . "%");
            }
        })->where("is_customer_service", 1)->where("is_group_leader", 2)->where("invite_relation", "like", "%-{$user_id}-%")->select(["id", "phone", "nickname", "reg_time", "code", "whats_app_account", "whats_app_link", "ip"])->offset($offset)->limit($limit)->get()->toArray();
    }

    public function countSearchAccount($where, $user_id)
    {
        return $this->Cx_User->where(function ($query) use ($where) {
            if (array_key_exists("nickname", $where) && $where["nickname"]) {
                $query->where("nickname", "like", "%" . $where["nickname"] . "%");
            }
            if (array_key_exists("phone", $where) && $where["phone"]) {
                $query->where("phone", "like", "%" . $where["phone"] . "%");
            }
            if (array_key_exists("ip", $where) && $where["ip"]) {
                $query->where("ip", "like", "%" . $where["ip"] . "%");
            }
        })->where("is_customer_service", 1)->where("invite_relation", "like", "%-{$user_id}-%")->count("id");
    }

    public function addAdmin($admin_data)
    {
        return $this->Cx_Admin->create($admin_data);
    }

    /**
     * 冻结用户账号
     * @param $user_id
     * @return mixed
     */
    public function frozen($user_id)
    {
        return $this->Cx_User->where("id", $user_id)->update(['status'=>1]);
    }

    /**
     * 解冻用户账号
     * @param $user_id
     * @return mixed
     */
    public function disFrozen($user_id)
    {
        return $this->Cx_User->where("id", $user_id)->update(['status'=>0]);
    }

    /**
     * 修改用户的推荐关系
     * @param $user_id
     * @param $relation
     * @return mixed
     */
    public function editInviteRelation($user_id, $relation)
    {
        return $this->Cx_User->where("id", $user_id)->update(['invite_relation'=>$relation]);
    }

    /**
     * 批量更新用户推荐关系
     * @param $user_id
     * @param $relation
     */
    public function editUserInviteRelation($user_id, $relation)
    {
        $list = $this->Cx_User->where('invite_relation', 'like', "%-$user_id-%")->select(['id', 'invite_relation'])->get()->toArray();
        foreach($list as $item){
            $temp_relation = trim(trim(explode("-{$user_id}-",$item['invite_relation'])[0],'-') . "-{$user_id}-" . trim($relation,'-'),'-');
            $new_relation = $temp_relation?'-' . $temp_relation . '-':"";
            $this->Cx_User->where("id", $item['id'])->update(['invite_relation'=>$new_relation]);
        }
    }
}
