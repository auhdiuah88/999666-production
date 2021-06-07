<?php


namespace App\Repositories\Ag;


use App\Models\Cx_Game;
use App\Models\Cx_Game_Betting;

class GameRepository
{

    protected $Cx_Game_betting, $Cx_Game;

    public function __construct
    (
        Cx_Game_Betting $cx_Game_Betting,
        Cx_Game $cx_Game
    )
    {
        $this->Cx_Game_betting = $cx_Game_Betting;
        $this->Cx_Game = $cx_Game;
    }

    public function getGame()
    {
        return $this->Cx_Game->select("id", "name")->get();
    }

    public function bettingList($where)
    {
        return makeModel($where, $this->Cx_Game_betting)
            ->with(
                [
                    'game_c_x' => function($query){
                        $query->select('id', 'name');
                    },
                    'users' => function($query){
                        $query->select('id', 'phone');
                    },
                    'game_name' => function($query){
                        $query->select('id', 'name');
                    },
                    'game_play' => function($query){
                        $query->select('id', 'number', 'prize_number');
                    }
                ]
            )
            ->select("id", "betting_num", "user_id", "game_id", "game_p_id", "game_c_x_id", "money", "betting_time", "status")
            ->orderByDesc("betting_time")
            ->paginate(15);
    }

}
