<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_Settings extends Model
{

    protected $table = 'settings';

    protected $primaryKey = "";

    protected $dateFormat  = "U";

    const UPDATED_AT = null;
    const CREATED_AT = "create_time";

    protected $guarded = [];

    public function getSettingValueAttribute($value){
        return empty($value)?[]:json_decode($value,true);
    }

    public function setSettingValueAttribute($value){
        $this->attributes['setting_value'] = json_encode(empty($value)?[]:$value);
    }

}
