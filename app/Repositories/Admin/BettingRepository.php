<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Game_Betting;
use App\Models\Cx_Game_Config;
use App\Models\Cx_Game_Play;
use App\Models\Cx_User;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class BettingRepository extends BaseRepository
{
    private $Cx_Game_Betting, $Cx_Game_Config, $Cx_Game_Play, $Cx_User;

    public function __construct(
        Cx_Game_Betting $game_Betting,
        Cx_Game_Config $config,
        Cx_Game_Play $cx_Game_Play,
        Cx_User $cx_User
    )
    {
        $this->Cx_Game_Betting = $game_Betting;
        $this->Cx_Game_Config = $config;
        $this->Cx_Game_Play = $cx_Game_Play;
        $this->Cx_User = $cx_User;
    }

    public function findAll($offset, $limit)
    {
        return $this->getModel()->orderByDesc("betting_time")->offset($offset)->limit($limit)->get()->toArray();
    }

    /**
     * 获取最新数据
     */
    public function getNewest()
    {
        return $this->getModel()->orderByDesc("betting_time")->limit(10)->get();
    }

    public function countAll()
    {
        return $this->Cx_Game_Betting->count("id");
    }

    public function sumAll($column)
    {
        return $this->Cx_Game_Betting->sum($column);
    }

    public function searchBettingLogs($data, $offset, $limit)
    {
        return $this->whereCondition($data, $this->getModel())->orderByDesc("betting_time")->offset($offset)->limit($limit)->get()->toArray();
    }

    public function countSearchBettingLogs($data)
    {
        return $this->whereCondition($data, $this->getModel())->count("id");
    }

    public function findPlayIds($value)
    {
        return array_column($this->Cx_Game_Config->where("name", $value)->get("id")->toArray(), "id");
    }

    public function findNumberId($number)
    {
        return array_column($this->Cx_Game_Play->where("number", $number)->get("id")->toArray(), "id");
    }

    public function findUserId($phone)
    {
        $user = $this->Cx_User->where("phone", $phone)->first();
        if (empty($user)) {
            return null;
        } else {
            return $user->id;
        }
    }

    public function getModel()
    {
        return $this->Cx_Game_Betting->with(["user" => function ($query) {
            $query->select(["id", "phone", "nickname"]);
        }, "game_name" => function ($query) {
            $query->select(["id", "name"]);
        }, "game_play" => function ($query) {
            $query->select(["id", "number", "prize_number"]);
        }, "game_c_x" => function ($query) {
            $query->select(["id", "name"]);
        }]);
    }
}
