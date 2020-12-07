<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_User extends Model
{
    protected $guarded = [];

    protected $table = "users";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $hidden = ['password'];

    const CACHE_USER_PROFILE = 'USER:';             // 个人信息保存在缓存中的键名

    public function getCustomerServiceIdAttribute($value)
    {
        if (!$value) {
            return $value;
        }
        $phone = self::where("id", $value)->select(["phone"])->first();
        if (!$phone) {
            return null;
        }
        return $phone->phone;
    }

    public function bank()
    {
        return $this->hasMany(Cx_User_Bank::class, "user_id", "id");
    }

    public function users()
    {
        return $this->belongsTo('App\Models\Cx_Game_Betting', "user_id");
    }

    public function betting_user()
    {
        return $this->hasMany(Cx_Charge_Logs::class, "betting_user_id", "id");
    }

    public function betting()
    {
        return $this->hasMany(Cx_Game_Betting::class, "user_id");
    }

    public function withdrawal()
    {
        return $this->hasMany(Cx_Withdrawal_Record::class, "user_id");
    }

    public function charge()
    {
        return $this->hasMany(Cx_Charge_Logs::class, "charge_user_id", "id");
    }

    public function balance_logs()
    {
        return $this->hasMany(Cx_User_Balance_Logs::class, "user_id");
    }
}
