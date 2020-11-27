<?php


namespace App\Repositories\Admin;


use App\Models\Cx_User;
use App\Repositories\BaseRepository;

class AccountRepository extends BaseRepository
{
    private $Cx_User;

    public function __construct(Cx_User $cx_User)
    {
        $this->Cx_User = $cx_User;
    }

    public function findAll($offset, $limit)
    {
        return $this->Cx_User->where("is_customer_service", 1)->select(["id", "phone", "nickname", "reg_time", "code", "whats_app_account", "whats_app_link"])->offset($offset)->limit($limit)->get()->toArray();
    }

    public function countAll()
    {
        return $this->Cx_User->where("is_customer_service", 1)->count("id");
    }

    public function findById($id)
    {
        return $this->Cx_User->where("id", $id)->where("is_customer_service", 1)->select(["id", "phone", "nickname", "reg_time", "code", "whats_app_account", "whats_app_link"])->first();
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

    public function searchAccount($where, $offset, $limit)
    {
        return $this->Cx_User->where($where)->where("is_customer_service", 1)->select(["id", "phone", "nickname", "reg_time", "code", "whats_app_account", "whats_app_link"])->offset($offset)->limit($limit)->get()->toArray();
    }

    public function countSearchAccount($where)
    {
        return $this->Cx_User->where($where)->where("is_customer_service", 1)->count("id");
    }
}
