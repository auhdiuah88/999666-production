<?php


namespace App\Repositories\Api;


use App\Models\Cx_Ads;
use App\Models\Cx_Game_Cates;
use App\Models\Cx_Game_List;
use App\Models\Cx_Game_Play;
use App\Models\Cx_System_Tips;
use Illuminate\Support\Facades\Redis;

class IndexRepository
{

    protected $Cx_Game_Cates, $Cx_Game_List, $Cx_System_Tips, $Cx_Game_Play, $Cx_Ads;

    public function __construct
    (
        Cx_Game_Cates $cx_Game_Cates,
        Cx_Game_List $cx_Game_List,
        Cx_System_Tips $cx_System_Tips,
        Cx_Game_Play $cx_Game_Play,
        Cx_Ads $cx_Ads
    )
    {
        $this->Cx_Game_Cates = $cx_Game_Cates;
        $this->Cx_Game_List = $cx_Game_List;
        $this->Cx_System_Tips = $cx_System_Tips;
        $this->Cx_Game_Play = $cx_Game_Play;
        $this->Cx_Ads = $cx_Ads;
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

    public function gameRecords(): array
    {
        $id_arr = [
            [
                'game_id' => 1,
                'name' => 'GOLD_TIME',
                'default' => 60,
            ],
            [
                'game_id' => 2,
                'name' => 'SILVER_TIME',
                'default' => 120,
            ],
            [
                'game_id' => 3,
                'name' => 'JEWELRY_TIME',
                'default' => 180,
            ],
            [
                'game_id' => 4,
                'name' => 'OTHER_TIME',
                'default' => 300,
            ]
        ];
        $data = [];
        foreach($id_arr as $val)
        {
            $minute = intval(env($val['name'], $val['default']) / 60);
            $data[] = array_merge($this->getGameRecord($val['game_id']), compact('minute'));
        }
        return $data;
    }

    protected function getGameRecord($game_id) : array
    {
        $list = $this->Cx_Game_Play
            ->where("game_id", $game_id)
            ->where("status",1)
            ->limit(24)
            ->orderBy('start_time', 'desc')
            ->select("id", "prize_number")
            ->get();
        $bq = $this->getCurGamePlay($game_id);
        return compact('list','bq');
    }

    protected function getCurGamePlay($id)
    {
        $time = time();
        $game_play =  Redis::get("CUR_GAME_PLAY_{$id}");
        if(!$game_play){
            $game_play = $this->Cx_Game_Play->where("game_id", $id)->where('start_time', "<=", $time)->where('end_time', ">", $time)->select(['b_money', 'end_time', 'game_id', 'id', 'is_status', 'number', 'start_time', 'type'])->first();
            if($game_play){
                Redis::setex("CUR_GAME_PLAY_{$id}", ($game_play->end_time-$time), json_encode($game_play));
            }
        }else{
            $game_play = json_decode($game_play);
        }
        return $game_play;
    }

    public function cateDetail($cid)
    {
        $where = [
            "pid" => ["=", $cid],
            "status" => ["=", 1]
        ];
        return makeModel($where, $this->Cx_Game_Cates)
            ->with(
                [
                    'games' => function($query)
                    {
                        $query
                            ->where("status", 1)
                            ->orderByDesc("sort")
                            ->select("id", "label", "icon", "link", "cid")
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
            ->orderByDesc("sort")
            ->select("id", "icon", "label")
            ->get();
    }

    public function adsDetail($id)
    {
        return $this->Cx_Ads->where("id", $id)->select("id", "title", "content", "status")->first();
    }

}
