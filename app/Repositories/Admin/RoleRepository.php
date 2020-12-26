<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Jurisdiction;
use App\Models\Cx_Role;

class RoleRepository
{
    protected $Cx_Role, $Cx_Jurisdiction;

    public function __construct(Cx_Role $role, Cx_Jurisdiction $jurisdiction)
    {
        $this->Cx_Role = $role;
        $this->Cx_Jurisdiction = $jurisdiction;
    }

    public function Add_Role($data)
    {
        return $this->Cx_Role->insertGetId($data);
    }

    public function Find_Jurisdiction_By_Id($id)
    {
        return $this->Cx_Role->where("id", $id)->select("jurisdiction")->first();
    }

    public function Find_Jurisdiction_By_Ids($ids)
    {
        return $this->Cx_Jurisdiction->whereIn("id", $ids)->get()->toArray();
    }

    public function Find_Role($id)
    {
        return $this->Cx_Role->where("id", $id)->first();
    }

    public function Edit_Role($data)
    {
        return $this->Cx_Role->where("id", $data['id'])->update($data);
    }

    public function Del_Role($id)
    {
        return $this->Cx_Role->where("id", $id)->delete();
    }

    public function Find_All_Role($limit, $offset)
    {
        return $this->Cx_Role->offset($offset)->limit($limit)->get()->toArray();
    }

    public function Count_Role()
    {
        return $this->Cx_Role->count();
    }

    public function getRoleIdByName(string $name): int
    {
        return $this->Cx_Role->where('rolename', $name)->value('id') ?? 0;
    }
}
