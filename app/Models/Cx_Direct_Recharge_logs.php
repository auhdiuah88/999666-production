<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_Direct_Recharge_logs extends Model
{

    protected $guarded = [];

    protected $table = "direct_recharge_logs";

    protected $primaryKey = "id";

    protected $dateFormat = 'U';

    public const UPDATED_AT = null;

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getExamTimeAttribute()
    {
        return $this->exam_time?date("Y-m-d H:i:s",$this->exam_time):"-";
    }

    public function image()
    {
        return $this->belongsTo('App\Models\Cx_Uploads','image_id','image_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Cx_User','user_id','id');
    }

    public function bank()
    {
        return $this->belongsTo('App\Models\Cx_Platform_Bank_Cards','bank_card_id','id');
    }

}
