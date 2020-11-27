<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cx_User_Recharge_Log extends Model
{
    protected $guarded = [];

    protected $table = "user_recharge_logs";

    protected $primaryKey = "id";

    public $timestamps = false;


}
