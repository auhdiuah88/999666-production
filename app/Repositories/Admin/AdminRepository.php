<?php

namespace App\Repositories\Admin;

use App\Models\Cx_Admin;
use App\Models\Cx_Role;
use App\Models\Cx_Jurisdiction;
use App\Models\Cx_White_List;
use Illuminate\Support\Facades\Redis;

class AdminRepository
{
    protected $Cx_Admin, $Cx_Role, $Cx_Jurisdiction, $Cx_White_List;
    protected $admin = "ADMIN:";
    protected $user = "ADMIN_USER:";

    public function __construct(Cx_Admin $Cx_Admin, Cx_Role $Cx_Role, Cx_Jurisdiction $Cx_Jurisdiction, Cx_White_List $cx_White_List)
    {
        $this->Cx_Admin = $Cx_Admin;
        $this->Cx_Role = $Cx_Role;
        $this->Cx_Jurisdiction = $Cx_Jurisdiction;
        $this->Cx_White_List = $cx_White_List;
    }

    // 查询IP是否在白名单
    public function getIp($ip)
    {
        return $this->Cx_White_List->where("ip", $ip)->first();
    }

    //根据用户名获取用户
    public function Get_User($username)
    {
        $data = $this->Cx_Admin->select('id', 'username', 'password', 'nickname', 'token', 'role_id')->where('username', $username)->first();
        if (!empty($data)) {
            return $data;
        } else {
            return false;
        }
    }

    //更新token
    public function Set_Token($admin_id, $token)
    {
        $this->Cx_Admin
            ->where('id', $admin_id)
            ->update(['token' => $token]);
    }

    // 更新状态
    public function Update_Status($id, $status)
    {
        $this->Cx_Admin->where("id", $id)->update(["status" => $status]);
    }

    public function Count_User()
    {
        return $this->Cx_Admin->count();
    }

    public function FindAllRole()
    {
        return $this->Cx_Role->get()->toArray();
    }

    public function Get_Role($role_id)
    {
        $role = $this->Cx_Role
            ->where('id', $role_id)
            ->first();
        $jurisdiction = explode(",", $role->jurisdiction);
        $menu = $this->Cx_Jurisdiction
            ->whereIn('id', $jurisdiction)
            ->where("parent_id", 0)
            ->get();
        $function = $this->Cx_Jurisdiction
            ->whereIn('id', $jurisdiction)
            ->where("parent_id", ">", 0)
            ->get();
        return [
            "role_name" => $role->rolename,
            "menu" => $menu,
            "function" => $function,

        ];
    }

    public function Redis_Get_Admin($userId, $key)
    {
        return Redis::hget($this->admin . $userId, $key);
    }

    public function Redis_Set_Admin($userId, $data)
    {
        Redis::hset($this->admin . $userId, "frequency", $data[0]);
        if ($data[1]) {
            Redis::hset($this->admin . $userId, "time", $data[1]);
        }
    }

    public function Redis_Del_Admin($userId)
    {
        Redis::hkeys($this->admin . $userId);
    }

    public function Redis_Set_Admin_User($data, $userId)
    {
        Redis::set($this->user . $userId, $data);
    }

    public function Redis_Get_Admin_User($userId)
    {
        return json_decode(Redis::get($this->user . $userId), true);
    }

    public function Redis_Del_Admin_User($userId)
    {
        Redis::del($this->user . $userId);
    }

    public function Add_Admin($data)
    {
        return $this->Cx_Admin->insertGetId($data);
    }

    public function Edit_Admin($data)
    {
        return $this->Cx_Admin->where("id", $data["id"])->update($data);
    }

    public function Prohibition_Admin($id)
    {
        return $this->Cx_Admin->where("id", $id)->update(["status" => 3]);
    }

    public function Relieve_Admin($id)
    {
        return $this->Cx_Admin->where("id", $id)->update(["status" => 0]);
    }

    public function Del_Admin($id)
    {
        return $this->Cx_Admin->where("id", $id)->delete();
    }

    public function Find_All_Admin($limit, $offset)
    {
        return $this->Cx_Admin->with(array('Role' => function ($query) {
                $query->select('id', 'rolename');
            })
        )->offset($offset)->limit($limit)->get()->toArray();
    }

    public function Find_By_Id_Admin($id)
    {
        $data = $this->Cx_Admin->with(array('Role' => function ($query) {
                $query->select('id', 'rolename');
            })
        )->where("id", $id)->first();
        return $data;
    }

    public function Find_By_Fd_Admin($fd)
    {
        $data = $this->Cx_Admin->with(array('Role' => function ($query) {
                $query->select('id', 'rolename');
            })
        )->where("fd", $fd)->first();
        return $data;
    }

    public function Update_Admin_Fd($id, $condition)
    {
        return $this->Cx_Admin->where("id", $id)->updata($condition);
    }

    public function getMenu($id)
    {
        $role_id = $this->Cx_Admin->select("role_id")->where("id", $id)->first();
        $role = $this->Cx_Role
            ->where('id', $role_id->role_id)
            ->first();
        $jurisdiction = explode(",", $role->jurisdiction);
        $menu = $this->Cx_Jurisdiction
            ->whereIn('id', $jurisdiction)
            ->where("parent_id", 0)
            ->get();
        return $menu;
    }

    public function findCustomerStatus()
    {
        return $this->Cx_Admin->where("customer_status", 1)->get()->toArray();
    }

    public function countCustomerStatus()
    {
        return $this->Cx_Admin->where("customer_status", 1)->count("id");
    }

    public function updateCustomerStatus($id, $status)
    {
        return $this->Cx_Admin->where("id", $id)->update(["customer_status" => $status]);
    }
}
