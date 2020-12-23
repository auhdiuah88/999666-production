<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cx_Admin_Operation_Log extends Model
{
    protected $table = 'admin_operation_log as log';
    protected $primaryKey = "id";
    public $timestamps = false;

    public function admin_user()
    {
        return $this->hasOne(Cx_Admin::class,'id','admin_id');
    }

    /**
     *  获取用户的姓名.
     *
     * @param  string  $value
     * @return string
     */
    public function getCTimeAttribute($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    public function getExecTimeAttribute($value)
    {
        return round($value / 10, 1) . 'ms';
    }

    public function getRequestParamsAttribute($value)
    {
        return json_encode(unserialize($value));
    }

    public function scopeCTimeRange($query)
    {


        var_dump(request());die;
        return $query;
    }
}
