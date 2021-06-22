<?php


namespace App\Repositories\Api;


use App\Models\Cx_Game_Cates;
use App\Models\Cx_Game_List;
use App\Models\Cx_Game_Play;
use App\Models\Cx_System_Tips;

class IndexRepository
{

    protected $Cx_Game_Cates, $Cx_Game_List, $Cx_System_Tips, $Cx_Game_Play;

    public function __construct
    (
        Cx_Game_Cates $cx_Game_Cates,
        Cx_Game_List $cx_Game_List,
        Cx_System_Tips $cx_System_Tips,
        Cx_Game_Play $cx_Game_Play
    )
    {
        $this->Cx_Game_Cates = $cx_Game_Cates;
        $this->Cx_Game_List = $cx_Game_List;
        $this->Cx_System_Tips = $cx_System_Tips;
        $this->Cx_Game_Play = $cx_Game_Play;
    }

    public function tips($where)
    {
        return makeModel($where, $this->Cx_System_Tips)
            ->orderByDesc('create_time')
            ->select('id', 'content')
            ->get();
    }

    public function gameCateList($where)
    {
        return makeModel($where, $this->Cx_Game_Cates)
            ->orderByDesc('is_rg')
            ->orderByDesc('sort')
            ->select("id", "icon", "label", "is_rg")
            ->with(
                [
                    'icon_url' => function($query)
                    {
                        $query->select("image_id", "path");
                    }
                ]
            )
            ->get();
    }

    public function getGameCateInfoById($id)
    {
        return $this->Cx_Game_Cates->where("id", $id)->select("id", "is_rg", "label", "pid")->first();
    }

    public function gameRecords()
    {
        $id_arr = [1, 2, 3, 4];
        $data = [];
        foreach($id_arr as $val)
        {

        }
    }

    protected function getGameRecord($game_id)
    {
        return $this->Cx_Game_Play->with(array(
                'game_name_p' => function ($query) {
                    $query->select('id', 'name');
                }
            )
        )->where("game_id", $game_id)->where("status",1)->offset($offset)->limit($limit)->orderBy('start_time', 'desc')->get();
    }

}
