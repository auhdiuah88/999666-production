<?php


namespace App\Models;


use App\Dictionary\GameDic;
use Illuminate\Database\Eloquent\Model;

class Cx_Game extends Model
{
    protected $table = "game";

    protected $primaryKey = "id";

    public $timestamps = false;

    public function getStatusAttribute($value)
    {
        if ($value == 0) {
            return "正常";
        } elseif ($value == 1) {
            return "下架";
        }
    }

    public function getIconAttribute($value)
    {
        if (!empty($value)) {
            return asset($value);
        }
    }

    public function getOpenTypeAttribute()
    {
        return GameDic::data($this->open_type);
    }

    public function game_name()
    {
        return $this->belongsTo('App\Models\Cx_Game_Betting', "game_id");
    }
    public function game_name_p()
    {
        return $this->belongsTo('App\Models\Cx_Game_Betting', "game_id");
    }


}
