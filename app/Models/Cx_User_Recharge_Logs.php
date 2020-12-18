<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_User_Recharge_Logs extends Model
{
    protected $table = "user_recharge_logs";

    protected $primaryKey = "id";

    public $timestamps = false;

    public function getMoneyAttribute(){
        if(isset($this->status) && $this->status == 2){
            return isset($this->arrive_money) ? $this->arrive_money : $this->money;
        }else{
            return $this->money;
        }
    }

    public function user(){
        return $this->belongsTo('App\Models\Cx_User','user_id','id');
    }
}
