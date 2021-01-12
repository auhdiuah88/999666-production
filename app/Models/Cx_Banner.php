<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cx_Banner extends Model
{
    const LOCATION_LIST = [
        1 => [
            'id' => 1,
            'name' => "首页",
        ],
        [
            'id' => 2,
            'name' => "活动",
        ],
    ];
    const TYPE_LIST = [
        1 => [
            'id' => 1,
            'name' => "不跳转",
        ],
        [
            'id' => 2,
            'name' => "内链",
        ],
        [
            'id' => 3,
            'name' => "外链",
        ],
    ];

    use SoftDeletes;

    protected $table = "banner";

    protected $primaryKey = "id";

    public $timestamps = false;

    /**
     * 获取与用户相关的电话记录。
     */
    public function uploads()
    {
        return $this->hasOne(Cx_Uploads::class, 'image_id', 'uploads_id');
    }

    public function getTypeNameAttribute()
    {
        $typeName = '';
        if (isset(self::TYPE_LIST[$this->type])) {
            $typeName = self::TYPE_LIST[$this->type]['name'];
        }
        return $typeName;
    }

    public function getLocationNameAttribute()
    {
        $name = '';
        if (isset(self::LOCATION_LIST[$this->location])) {
            $name = self::LOCATION_LIST[$this->location]['name'];
        }
        return $name;
    }

    protected $appends = ['type_name', 'location_name'];


}
