<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Game_Cates;

class GameRepository
{

    protected $Cx_Game_Cates;

    public function __construct
    (
        Cx_Game_Cates $cx_Game_Cates
    )
    {
        $this->Cx_Game_Cates = $cx_Game_Cates;
    }

    public function cateList($where)
    {
        return makeModel($where, $this->Cx_Game_Cates)
            ->select("id", "label", "icon", "status", "sort", "pid", "create_time", "update_time", "is_rg")
            ->with(
                [
                    'children' => function($query)
                    {
                        $query
                            ->select("id", "label", "icon", "status", "sort", "pid", "create_time", "update_time", "is_rg")
                            ->orderByDesc('sort')
                            ->with(
                                [
                                    'icon_url' => function($query)
                                    {
                                        $query->select("image_id", "path");
                                    }
                                ]
                            );
                    },
                    'icon_url' => function($query)
                    {
                        $query->select("image_id", "path");
                    }
                ]
            )
            ->orderByDesc('sort')
            ->get();
    }

    public function addCate($data)
    {
        return $this->Cx_Game_Cates->create($data);
    }

    public function editCate($id, $data)
    {
        return $this->Cx_Game_Cates->where("id", $id)->update($data);
    }

    public function delCate($id)
    {
        return $this->Cx_Game_Cates->where("id", $id)->delete();
    }

    public function cateDetail($where)
    {
        return makeModel($where, $this->Cx_Game_Cates)
            ->with(
                [
                    'icon_url' => function($query)
                    {
                        $query->select("image_id", "path");
                    }
                ]
            )
            ->first();
    }

}
