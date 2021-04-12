<?php


namespace App\Repositories\Admin;


use App\Models\Cx_User;
use App\Models\Cx_User_Bank;

class BankRepository
{
    private $Cx_User_Bank, $Cx_User;

    public function __construct(Cx_User_Bank $bank, Cx_User $cx_User)
    {
        $this->Cx_User_Bank = $bank;
        $this->Cx_User = $cx_User;
    }

    public function findAll($offset, $limit)
    {
        return $this->Cx_User_Bank->with(["user" => function ($query) {
            $query->select("id", "nickname", "phone");
        }])->offset($offset)->limit($limit)->get()->toArray();
    }

    public function countAll()
    {
        return $this->Cx_User_Bank->count("id");
    }

    public function findById($id)
    {
        return $this->Cx_User_Bank->with(["user" => function ($query) {
            $query->select("id", "nickname");
        }])->where("id", $id)->first();
    }

    public function addBank($data)
    {
        return $this->Cx_User_Bank->insertGetId($data);
    }

    public function editBank($data)
    {
        return $this->Cx_User_Bank->where("id", $data["id"])->update($data);
    }

    public function delBank($id)
    {
        return $this->Cx_User_Bank->where("id", $id)->delete();
    }

    public function getBankUserIds($phone)
    {
        return $this->Cx_User->where("phone", $phone)->select("id")->first();
    }

    public function findBankByUserId($id,$account_holder, $offset, $limit)
    {
        $where = [];
        if($id)
            $where["user_id"] = ["=",$id];
        if($account_holder)
            $where["account_holder"] = ["=",$account_holder];
        return makeModel($where,$this->Cx_User_Bank)->with(["user" => function ($query) {
            $query->select("id", "nickname", "phone");
        }])->offset($offset)->limit($limit)->get()->toArray();
    }

    public function countBankByUserId($id,$account_holder)
    {
        $where = [];
        if($id)
            $where["user_id"] = ["=",$id];
        if($account_holder)
            $where["account_holder"] = ["=",$account_holder];
        return makeModel($where,$this->Cx_User_Bank)->count();
    }
}
