<?php


namespace App\Repositories\Api;


use App\Models\Cx_User_Address;

class AddressRepository
{
    private $Cx_User_Address;

    public function __construct(Cx_User_Address $address)
    {
        $this->Cx_User_Address = $address;
    }

    public function findAll($id)
    {
        return $this->Cx_User_Address->where("user_id", $id)->get()->toArray();
    }

    public function findById($id)
    {
        return $this->Cx_User_Address->where("id", $id)->first();
    }

    public function addAddress($data)
    {
        return $this->Cx_User_Address->insertGetId($data);
    }

    public function editAddress($data)
    {
        return $this->Cx_User_Address->where("id", $data["id"])->update($data);
    }

    public function delAddress($id)
    {
        return $this->Cx_User_Address->where("id", $id)->delete();
    }
}
