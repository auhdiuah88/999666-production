<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_Withdrawal_Record extends Model
{
    protected $table = "withdrawal_record";

    protected $primaryKey = "id";

    public $timestamps = false;

    public function bank()
    {
        return $this->belongsTo(Cx_User_Bank::class, "bank_id", "id");
    }

    public function user()
    {
        return $this->belongsTo(Cx_User::class, "user_id", "id");
    }
}
