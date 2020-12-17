<?php


namespace App\Models;


use App\Dictionary\BalanceTypeDic;
use Illuminate\Database\Eloquent\Model;

class Cx_User_Balance_Logs extends Model
{
    protected $guarded = [];

    protected $table = "user_balance_logs";

    protected $primaryKey = "id";

    public $timestamps = false;

    public function getTypeMapAttribute(){
        return BalanceTypeDic::data($this->type);
    }

    public function user()
    {
        return $this->belongsTo(Cx_User::class, "user_id", "id");
    }

    public function admin()
    {
        return $this->hasOne(Cx_Admin::class, "id", "admin_id");
    }
}
