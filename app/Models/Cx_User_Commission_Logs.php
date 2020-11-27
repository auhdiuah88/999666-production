<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_User_Commission_Logs extends Model
{
    protected $table = "user_commission_logs";

    protected $primaryKey = "id";

    public $timestamps = false;
}
