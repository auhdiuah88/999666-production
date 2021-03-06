<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Game_Betting;
use App\Models\Cx_Game_Play;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class PeriodRepository extends BaseRepository
{
    private $Cx_Game_Play, $Cx_Game_Betting;

    public function __construct
    (
        Cx_Game_Play $cx_Game_Play,
        Cx_Game_Betting $cx_Game_Betting
    )
    {
        $this->Cx_Game_Play = $cx_Game_Play;
        $this->Cx_Game_Betting = $cx_Game_Betting;
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
            if(isset($data['conditions']['prize_time']) && $data['conditions']['prize_time']){
                $data['conditions']['end_time'] = $data['conditions']['prize_time'];
                unset($data['conditions']['prize_time']);
                $data['ops']['end_time'] = 'between';
            }
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
        if($data['conditions']['game_id'] == 0)unset($data['conditions']['game_id']);
        if(isset($data['conditions']['status']) && $data['conditions']['status'] == 0){
            if(isset($data['conditions']['prize_time']) && $data['conditions']['prize_time']){
                $data['conditions']['end_time'] = $data['conditions']['prize_time'];
                unset($data['conditions']['prize_time']);
                $data['ops']['end_time'] = 'between';
            }
            $count = $this->whereCondition($data, $this->Cx_Game_Play)->count("id");
        }else{
            $count = $this->whereCondition($data, $this->Cx_Game_Play)->where("status", 1)->count("id");
        }
        return $count;
    }

    public function findById($id)
    {
        return $this->Cx_Game_Play->where("id", $id)->first();
    }

    public function findRelById($id, $test_user_ids)
    {
        $prefix = DB::getConfig('prefix');
        $data = DB::selectOne('select sum(gb.money - gb.win_money) as cha, sum(gb.money) as total_betting_money, sum(gb.win_money) as total_win_money from `'.$prefix.'game_betting` gb where gb.game_p_id = '.$id.' and gb.user_id NOT IN ('. implode(',',$test_user_ids) .')');
        $betting_times = DB::selectOne('select count(gb.id) as betting_times from `'.$prefix.'game_betting` gb where gb.game_p_id = '.$id.' and gb.user_id NOT IN ('. implode(',',$test_user_ids) .') group by gb.user_id');
        $data->betting_times = $betting_times->betting_times ?? 0;
        return $data;
    }

    public function planTaskList($where, $size)
    {
        $data = makeModel($where, $this->Cx_Game_Play)
            ->select(['id', 'number', 'start_time', 'end_time', 'prize_number', 'game_id'])
            ->with(
                [
                    'game' => function($query){
                        $query->select(['id', 'name']);
                    }
                ]
            )
            ->orderBy('end_time', 'asc')
            ->paginate($size);
        foreach($data as &$item){
            $item->start_time = date('Y-m-d H:i:s', $item->start_time);
            $item->end_time = date('Y-m-d H:i:s', $item->end_time);
        }
        return $data;
    }

    public function exportTask($where, $size, $page)
    {
        $data = makeModel($where, $this->Cx_Game_Play)
            ->select(['id', 'number', 'start_time', 'end_time', 'prize_number', 'game_id'])
            ->with(
                [
                    'game' => function($query){
                        $query->select(['id', 'name']);
                    }
                ]
            )
            ->offset(($page - 1) * $size)
            ->limit($size)
            ->orderBy('end_time', 'asc')
            ->get();
        if($data->isEmpty())return [];
        $data = $data->toArray();
        date_default_timezone_set("Asia/Shanghai");
        foreach($data as &$item){
            $item['number'] = "'" . (string)$item['number'];
            $item['start_time'] = date('Y-m-d H:i:s', $item['start_time']);
            $item['end_time'] = date('Y-m-d H:i:s', $item['end_time']);
            $item['game'] = $item['game']['name'];
            unset($item['game_id']);
        }
        return $data;
    }

    public function getPeriodAndLock($where)
    {
        return makeModel($where, $this->Cx_Game_Play)->sharedLock()->first();
    }

}
