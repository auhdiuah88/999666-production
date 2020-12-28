<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cx_Admin extends Model
{

    use SoftDeletes;

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

    public function user()
    {
        return $this->belongsTo(Cx_User::class,'user_id','id');
    }
}
