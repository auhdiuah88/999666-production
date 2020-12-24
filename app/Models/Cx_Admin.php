<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_Admin extends Model
{
    protected $table = "admin";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $guarded = [];

    public function Role()
    {
        return $this->belongsTo(Cx_Role::class, "role_id",'id');
    }

    public function balance()
    {
        return $this->belongsTo(Cx_User_Balance_Logs::class, "admin_id");
    }
}
