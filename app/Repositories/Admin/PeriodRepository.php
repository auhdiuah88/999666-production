<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Game_Play;
use App\Repositories\BaseRepository;

class PeriodRepository extends BaseRepository
{
    private $Cx_Game_Play;

    public function __construct(Cx_Game_Play $cx_Game_Play)
    {
        $this->Cx_Game_Play = $cx_Game_Play;
    }

    public function findAll($offset, $limit, $status)
    {
        return $this->Cx_Game_Play->where("game_id", $status)->select(["id", "number", "prize_number", "status", "prize_time", "end_time", "is_status"])->orderByDesc("prize_time")->offset($offset)->limit($limit)->get()->toArray();
    }

    /**
     * 获取最新的数据
     */
    public function getNewest($game_id)
    {
        return $this->Cx_Game_Play->where("game_id", $game_id)->select(["id", "number", "prize_number", "status", "prize_time", "end_time", "is_status"])->orderByDesc("prize_time")->limit(10)->get();
    }

    public function countAll($status)
    {
        return $this->Cx_Game_Play->where("game_id", $status)->count("id");
    }

    public function searchPeriod($data, $offset, $limit)
    {
        return $this->whereCondition($data, $this->Cx_Game_Play)->select(["id", "number", "prize_number", "status", "prize_time", "end_time", "is_status"])->orderBy("end_time", "asc")->offset($offset)->limit($limit)->get()->toArray();
    }

    public function countSearchPeriod($data)
    {
        return $this->whereCondition($data, $this->Cx_Game_Play)->count("id");
    }

    public function findById($id)
    {
        return $this->Cx_Game_Play->where("id", $id)->first();
    }
}
