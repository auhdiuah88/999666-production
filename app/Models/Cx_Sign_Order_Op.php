<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_Sign_Order_Op extends Model
{
    protected $table = "sign_order_op";

    protected $primaryKey = "id";

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(Cx_User::class, "user_id");
    }
}
