<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_User_Recharge_Logs extends Model
{
    protected $table = "user_recharge_logs";

    protected $primaryKey = "id";

    public $timestamps = false;

    public function getPhoneHideAttribute($value){
        return hide($value, 3, 4);
    }

    public function getMoneyAttribute($value){
        if(isset($this->status) && $this->status == 2){
            return isset($this->arrive_money) ? $this->arrive_money : $value;
        }else{
            return $value;
        }
    }

    public function user(){
        return $this->belongsTo('App\Models\Cx_User','user_id','id');
    }
}
