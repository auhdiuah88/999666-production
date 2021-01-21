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
        if($status){
            return $this->Cx_Game_Play
                ->where("game_id", $status)
                ->where("status", 1)
                ->with([
                    'game_name_p' => function($query){
                        $query->select(['id', 'name']);
                    }
                ])
                ->select(["id", "number", "prize_number", "status", "prize_time", "end_time", "is_status", 'game_id'])
                ->orderBy("prize_time", "desc")
                ->orderBy("end_time", "desc")
                ->offset($offset)
                ->limit($limit)
                ->get()
                ->toArray();
        }else{
            return $this->Cx_Game_Play
                ->where("status", 1)
                ->select(["id", "number", "prize_number", "status", "prize_time", "end_time", "is_status", 'game_id'])
                ->with([
                    'game_name_p' => function($query){
                        $query->select(['id', 'name']);
                    }
                ])
                ->orderBy("prize_time", "desc")
                ->orderBy("end_time", "desc")
                ->offset($offset)
                ->limit($limit)
                ->get()
                ->toArray();
        }
    }

    /**
     * 获取最新的数据
     */
    public function getNewest($game_id)
    {
        if($game_id){
            return $this->Cx_Game_Play
                ->where("game_id", $game_id)
                ->with([
                    'game_name_p' => function($query){
                        $query->select(['id', 'name']);
                    }
                ])
                ->select(["id", "number", "prize_number", "status", "prize_time", "end_time", "is_status", "game_id"])
                ->orderByDesc("id")
                ->limit(10)
                ->get();
        }else{
            return $this->Cx_Game_Play
                ->select(["id", "number", "prize_number", "status", "prize_time", "end_time", "is_status", "game_id"])
                ->with([
                    'game_name_p' => function($query){
                        $query->select(['id', 'name']);
                    }
                ])
                ->orderByDesc("id")
                ->limit(10)
                ->get();
        }
    }

    public function countAll($status)
    {
        if($status){
            return $this->Cx_Game_Play->where("game_id", $status)->where("status", 1)->count("id");
        }else{
            return $this->Cx_Game_Play->where("status", 1)->count("id");
        }
    }

    public function searchPeriod($data, $offset, $limit)
    {
        if($data['conditions']['game_id'] == 0)unset($data['conditions']['game_id']);
        if(isset($data['conditions']['status']) && $data['conditions']['status'] == 0){
            return $this->whereCondition($data, $this->Cx_Game_Play)
                ->with([
                    'game_name_p' => function($query){
                        $query->select(['id', 'name']);
                    }
                ])
                ->select(["id", "number", "prize_number", "status", "prize_time", "end_time", "is_status", 'game_id'])
                ->orderBy("id", "asc")
                ->offset($offset)
                ->limit($limit)
                ->get()
                ->setAppends(['prize_sd_btn'])
                ->toArray();
        }else{
            return $this->whereCondition($data, $this->Cx_Game_Play)
                ->where("status", 1)
                ->with([
                    'game_name_p' => function($query){
                        $query->select(['id', 'name']);
                    }
                ])
                ->select(["id", "number", "prize_number", "status", "prize_time", "end_time", "is_status", 'game_id'])
                ->orderBy("prize_time", "desc")
                ->orderBy("end_time", "desc")
                ->offset($offset)
                ->limit($limit)
                ->get()
                ->setAppends(['prize_sd_btn'])
                ->toArray();
        }
    }

    public function countSearchPeriod($data)
    {
        if(isset($data['conditions']['status']) && $data['conditions']['status'] == 0){
            return $this->whereCondition($data, $this->Cx_Game_Play)->count("id");
        }else{
            return $this->whereCondition($data, $this->Cx_Game_Play)->where("status", 1)->count("id");
        }
    }

    public function findById($id)
    {
        return $this->Cx_Game_Play->where("id", $id)->first();
    }
}
