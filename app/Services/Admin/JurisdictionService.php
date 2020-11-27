<?php


namespace App\Services\Admin;


use App\Repositories\Admin\JurisdictionRepository;

class JurisdictionService
{
    protected $JurisdictionRepository;

    public function __construct(JurisdictionRepository $jurisdictionRepository)
    {
        $this->JurisdictionRepository = $jurisdictionRepository;
    }

    public function FindAll()
    {
        $data = $this->JurisdictionRepository->Get_Jurisdiction();
        $list = $this->deal_list_to_tree($data);
        $total = $this->JurisdictionRepository->Count_Jurisdiction();
        return array("total" => $total, "list" => $list);
    }

    public function Add($data)
    {
        if ($data["parent_id"] == null) {
            $data["parent_id"] = 0;
        }
        return $this->JurisdictionRepository->Add_Jurisdiction($data);
    }

    public function Edit($data)
    {
        if ($data["parent_id"] == null) {
            $data["parent_id"] = 0;
        }
        return $this->JurisdictionRepository->Edit_Jurisdiction($data);
    }

    public function FindById($id)
    {
        return $this->JurisdictionRepository->Get_Jurisdiction_By_Id($id);
    }

    public function RightAll()
    {
        $data = $this->JurisdictionRepository->RightAll();
        $list = $this->deal_list_to_tree($data);
        return $list;
    }

    public function FindRightAll()
    {
        $data = $this->JurisdictionRepository->RightAll();
        return $data;
    }


    /**
     * 将一个一维数组转换成多维数组
     */
    public function deal_list_to_tree($data, $pkName = 'id', $pIdName = 'parent_id', $childName = 'children_list', $is_empty_childrens = false, $rootId = '')
    {
        $new_data = [];
        foreach ($data as $sorData) {
            if ($sorData[$pIdName] == $rootId) {
                $res = $this->deal_list_to_tree($data, $pkName, $pIdName, $childName, $is_empty_childrens, $sorData[$pkName]);
                if (!empty($res) && !$is_empty_childrens) {
                    if (array_key_exists($childName, $sorData)) {
                        if (array_key_exists($childName, $sorData)) {
                            $sorData[$childName][] = $res[0];
                        } else {
                            $sorData[$childName][] = $res;
                        }
                    } else {
                        $sorData[$childName] = $res;
                    }
                }
                $new_data[] = $sorData;
            }
        }
        return $new_data;
    }
}