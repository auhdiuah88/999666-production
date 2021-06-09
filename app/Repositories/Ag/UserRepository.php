<?php


namespace App\Repositories\Ag;


use App\Models\Cx_Ag_Link;
use App\Models\Cx_User;
use Illuminate\Support\Facades\Cookie;

class UserRepository
{

    protected $Cx_Users, $Cx_Ag_Link;

    public function __construct
    (
        Cx_User $cx_User,
        Cx_Ag_Link $cx_Ag_Link
    )
    {
        $this->Cx_Users = $cx_User;
        $this->Cx_Ag_Link = $cx_Ag_Link;
    }

    public function getById($id)
    {
        return $this->Cx_Users->where("id", $id)->first();
    }

    public function getByPhone($phone)
    {
        return $this->Cx_Users->where("phone", $phone)->first();
    }

    public function getMemberByPhone($phone)
    {
        $user_id = Cookie::get('user')['id'];
        return $this->Cx_Users->where("phone", $phone)->where("invite_relation", "like", "%-{$user_id}-%")->first();
    }

    public function addLink($data)
    {
        $data['created_at'] = time();
        return $this->Cx_Ag_Link->create($data);
    }

    public function getLinkList()
    {
        $user_id = Cookie::get('user')['id'];
        return $this->Cx_Ag_Link->where("user_id", $user_id)->orderByDesc('created_at')->select("id", "link", "type", "rebate_percent", "created_at")->paginate(10);
    }

    public function delLink($id)
    {
        return $this->Cx_Ag_Link->where("id", $id)->update(['deleted_at'=>date("Y-m-d H:i:s")]);
    }

    public function getUserList($phone, $user_type)
    {
        $user_id = Cookie::get('user')['id'];
        $where = [
            'invite_relation' => ['like', "%-{$user_id}-%"]
        ];
        if($phone)
        {
            $where['phone'] = ['=', $phone];
        }
        if($user_type > 0)
        {
            $where['user_type'] = ['=', $user_type];
        }
        $list = makeModel($where,$this->Cx_Users)->select("id", "phone", "user_type", "balance", "rebate_rate", "last_time", "reg_time")->orderByDesc("reg_time")->paginate(20);
        foreach($list as &$item)
        {
            $item->member = $this->countMember($item->id);
        }
        return $list;
    }

    public function countMember($user_id)
    {
        return $this->Cx_Users->where("invite_relation", "like", "%-{$user_id}-%")->count("id");
    }

    public function getMemberUserIds($user_id=0)
    {
        $user_id = $user_id?: Cookie::get('user')['id'];
        $where = [
            'invite_relation' => ['like', "%-{$user_id}-%"]
        ];
        return makeModel($where, $this->Cx_Users)->pluck('id')->toArray();
    }

}
