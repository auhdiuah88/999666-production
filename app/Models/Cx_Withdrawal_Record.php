<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_Withdrawal_Record extends Model
{
    protected $table = "withdrawal_record";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected static $payStatusArr = [
        0 => [
            'value' => 0,
            'text' => '未支付'
        ],
        1 => [
            'value' => 1,
            'text' => '已支付'
        ],
        2 => [
            'value' => 2,
            'text' => '已失效'
        ],
        3 => [
            'value' => 3,
            'text' => '订单异常'
        ],
        4 => [
            'value' => 4,
            'text' => '支付失败'
        ]
    ];

    public function bank()
    {
        return $this->belongsTo(Cx_User_Bank::class, "bank_id", "id");
    }

    public function user()
    {
        return $this->belongsTo(Cx_User::class, "user_id", "id");
    }

    public function getPayStatusJsonAttribute()
    {
        return self::$payStatusArr[$this->pay_status];
    }
}
