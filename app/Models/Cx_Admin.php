<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_Admin extends Model
{
    protected $table = "admin";

    protected $primaryKey = "id";

    public $timestamps = false;

    public function Role()
    {
        return $this->hasOne(Cx_Role::class, "id");
    }

    public function balance()
    {
        return $this->belongsTo(Cx_User_Balance_Logs::class, "admin_id", "id");
    }
}
