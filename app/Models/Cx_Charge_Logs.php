<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_Charge_Logs extends Model
{
    protected $table = "charge_logs";

    protected $primaryKey = "id";

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(Cx_User::class, "betting_user_id", "id");
    }

    public function charge_user()
    {
        return $this->belongsTo(Cx_User::class, "charge_user_id", "id");
    }
}
