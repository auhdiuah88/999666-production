<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_User_Balance_Logs extends Model
{
    protected $guarded = [];

    protected $table = "user_balance_logs";

    protected $primaryKey = "id";

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(Cx_User::class, "user_id", "id");
    }
}
