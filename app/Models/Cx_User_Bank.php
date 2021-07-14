<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_User_Bank extends Model
{
    protected $table = "user_bank";

    protected $primaryKey = "id";

    public $timestamps = false;

    public function withdrawal()
    {
        return $this->hasMany(Cx_Withdrawal_Record::class, "bank_id", "id");
    }

    public function user()
    {
        return $this->belongsTo(Cx_User::class, "user_id", "id");
    }

    public function bank()
    {
        return $this->belongsTo(Cx_Banks::class,"bank_type_id","bank_name");
    }
}
