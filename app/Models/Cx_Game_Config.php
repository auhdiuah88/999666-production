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
        '幸运' => 'Lucky',
        '0' => '0',
        '1' => '1',
        '2' => '2',
        '4' => '4',
        '5' => '5',
        '6' => '6',
        '7' => '7',
        '8' => '8',
        '9' => '9',
    ];

    public function game_c_x()
    {
        return $this->belongsTo('App\Models\Cx_Game_Betting',"game_c_x_id");
    }

    public function getNameIndiaAttribute($value){
        return $this->nameIndia[$value];
    }

}
