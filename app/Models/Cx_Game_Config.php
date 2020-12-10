<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_Game_Config extends Model
{
    protected $table = "game_config";

    protected $primaryKey = "id";
    public $timestamps = false;

    protected $nameIndia = [
        '奇数' => 'Odd',
        '偶数' => 'Even',
        '幸运' => 'Lucky'
    ];

    public function game_c_x()
    {
        return $this->belongsTo('App\Models\Cx_Game_Betting',"game_c_x_id");
    }

    public function getNameIndiaAttribute($value){
        if(!intval($value))return $this->nameIndia[$value];
    }

}
