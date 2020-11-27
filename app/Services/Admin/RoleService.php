<?php


namespace App\Services\Admin;


use App\Repositories\Admin\JurisdictionRepository;
use App\Repositories\Admin\RoleRepository;

class RoleService
{
    protected $RoleRepository, $JurisdictionRepository;

    public function __construct(RoleRepository $roleRepository, JurisdictionRepository $jurisdictionRepository)
    {
        $this->RoleRepository = $roleRepository;
        $this->JurisdictionRepository = $jurisdictionRepository;
    }

    public function Add($data)
    {
        return $this->RoleRepository->Add_Role($data);
    }

    public function Edit($data)
    {
        return $this->RoleRepository->Edit_Role($data);
    }

    public function FindAll($limit, $page)
    {
        $list = $this->RoleRepository->Find_All_Role($limit, ($page - 1) * $limit);
        $total = $this->RoleRepository->Count_Role();
        foreach ($list as $key => $item) {
            $jurisdiction = $this->JurisdictionRepository->Get_Jurisdiction_By_Ids(explode(",", $item["jurisdiction"]));
            $jurisdictions = [];
            foreach ($jurisdiction as $value) {
                $permission_name = "<span style='background-color: green; padding:2px 4px; border-radius: 3px; color: #fff; font-size: 12px;'> " . $value["permission_name"] . "</span>";
                array_push($jurisdictions, $permission_name);
            }
            $jurisdictions = implode(" ", $jurisdictions);
            unset($list[$key]["jurisdiction"]);
            $list[$key]["jurisdiction"] = $jurisdictions;
        }
        return array("total" => $total, "list" => $list);
    }

    public function Del($id)
    {
        return $this->RoleRepository->Del_Role($id);
    }

    public function FindById($id)
    {
        $result = $this->RoleRepository->Find_Role($id);
        return $result;
    }
}