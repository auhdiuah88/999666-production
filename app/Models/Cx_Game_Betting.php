<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_Game_Betting extends Model
{
    protected $table = "game_betting";

    protected $primaryKey = "id";

    public $timestamps = false;

//    public function game_c_w()
//    {
//        return $this->hasOne('App\Models\Cx_Game_Config',"id","game_c_w_id");
//    }
    public function game_c_x()
    {
        return $this->hasOne('App\Models\Cx_Game_Config', "id", "game_c_x_id");
    }

    public function users()
    {
        return $this->hasOne('App\Models\Cx_User', "id", "user_id");
    }

    public function game_name()
    {
        return $this->hasOne('App\Models\Cx_Game', "id", "game_id");
    }

    public function game_play()
    {
        return $this->hasOne('App\Models\Cx_Game_Play', "id", "game_p_id");
    }

    public function user()
    {
        return $this->belongsTo(Cx_User::class, "user_id", "id");
    }
}
