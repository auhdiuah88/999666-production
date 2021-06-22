<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Game_Cates;
use App\Models\Cx_Game_List;

class GameRepository
{

    protected $Cx_Game_Cates, $Cx_Game_List;

    public function __construct
    (
        Cx_Game_Cates $cx_Game_Cates,
        Cx_Game_List $cx_Game_List
    )
    {
        $this->Cx_Game_Cates = $cx_Game_Cates;
        $this->Cx_Game_List = $cx_Game_List;
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

    public function gameCateList($where)
    {
        return makeModel($where, $this->Cx_Game_Cates)
            ->select("id", "label", "pid", "id as value")
            ->with(
                [
                    'children' => function($query)
                    {
                        $query
                            ->select("id", "label", "pid", "id as value")
                            ->orderByDesc('sort');
                    }
                ]
            )
            ->orderByDesc('sort')
            ->get();
    }

    public function parentCateList($where)
    {
        return makeModel($where, $this->Cx_Game_Cates)
            ->select("id", "label")
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

    public function cateGetChildren($pid)
    {
        $ids = $this->Cx_Game_Cates->where("pid",$pid)->pluck('id');
        $ids[] = $pid;
        return $ids;
    }

    public function gameList($where, $size)
    {
        return makeModel($where, $this->Cx_Game_List)
            ->select("id", "cid", 'label', "icon", 'create_time', 'update_time', "sort", "status", "link")
            ->with(
                [
                    'icon_url' => function($query)
                    {
                        $query->select("image_id", "path");
                    },
                    'cate' => function($query)
                    {
                        $query->select("id", "label", "pid")->with(
                            [
                                'parent' => function($query)
                                {
                                    $query->select("id", "label", "pid");
                                }
                            ]
                        );
                    }
                ]
            )
            ->orderByDesc("sort")
            ->paginate($size);
    }

    public function addGame($data)
    {
        return $this->Cx_Game_List->create($data);
    }

    public function editGame($data, $id)
    {
        return $this->Cx_Game_List->where("id", $id)->update($data);
    }

    public function delGame($id)
    {
        return $this->Cx_Game_List->where("id", $id)->delete();
    }

}
