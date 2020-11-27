<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Jurisdiction;

class JurisdictionRepository
{
    protected $Cx_Jurisdiction;

    public function __construct(Cx_Jurisdiction $jurisdiction)
    {
        $this->Cx_Jurisdiction = $jurisdiction;
    }

    public function Get_Jurisdiction()
    {
        return $this->Cx_Jurisdiction->get()->toArray();
    }

    public function Get_Jurisdiction_By_Id($id)
    {
        return $this->Cx_Jurisdiction->where("id", $id)->first();
    }

    public function Count_Jurisdiction()
    {
        return $this->Cx_Jurisdiction->count();
    }

    public function Add_Jurisdiction($data)
    {
        return $this->Cx_Jurisdiction->insertGetId($data);
    }

    public function Edit_Jurisdiction($data)
    {
        return $this->Cx_Jurisdiction->where("id", $data["id"])->update($data);
    }

    public function RightAll()
    {
        return $this->Cx_Jurisdiction->get()->toArray();
    }

    public function Get_Jurisdiction_By_Path($path)
    {
        return $this->Cx_Jurisdiction->where("address", $path)->first();
    }

    public function Get_Jurisdiction_By_Ids($ids)
    {
        return $this->Cx_Jurisdiction->whereIn("id", $ids)->get()->toArray();
    }
}
