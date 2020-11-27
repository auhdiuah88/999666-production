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
        return $this->hasOne('App\Models\Cx_Game',"id","game_id");
    }
}
