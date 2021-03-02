<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_Game_Play extends Model
{
    protected $table = "game_play";

    protected $primaryKey = "id";

    public $timestamps = false;

    public function game_play()
    {
        return $this->belongsTo('App\Models\Cx_Game_Betting', "game_p_id");
    }

    public function game_name_p()
    {
        return $this->hasOne('App\Models\Cx_Game', "id", "game_id");
    }

    public function game()
    {
        return $this->belongsTo(Cx_Game::class,'game_id','id');
    }

    /**
     * 是否可以sd开奖
     * @return bool
     */
    public function getPrizeSdBtnAttribute()
    {
        //未开 且 离开奖时间至少10秒
        return !$this->status && (($this->end_time - 10) > time());
    }
}
