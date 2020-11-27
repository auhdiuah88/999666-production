<?php


namespace App\Repositories\Api;


use App\Models\Cx_User;
use App\Models\Cx_User_Bank;

class InfoRepository
{
    private $Cx_User, $Cx_User_Bank;

    public function __construct(Cx_User $cx_User, Cx_User_Bank $bank)
    {
        $this->Cx_User = $cx_User;
        $this->Cx_User_Bank = $bank;
    }

    public function findById($id)
    {
        return $this->Cx_User->where("id", $id)->first();
    }

    public function findBankById($id)
    {
        return $this->Cx_User_Bank->where("user_id", $id)->get()->toArray();
    }

    public function findBankFirst($id)
    {
        return $this->Cx_User_Bank->where("id", $id)->first();
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

    public function editUser($data)
    {
        return $this->Cx_User->where("id", $data["id"])->update($data);
    }
}
