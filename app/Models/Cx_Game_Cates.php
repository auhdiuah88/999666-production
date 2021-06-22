<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cx_Game_Cates extends Model
{

    protected $table = "game_cates";

    protected $primaryKey = "id";

    protected $dateFormat = "U";

    use SoftDeletes;

    protected $guarded = [];

    public const UPDATED_AT = "update_time";
    public const CREATED_AT = "create_time";

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function children()
    {
        return $this->hasMany('\App\Models\Cx_Game_Cates','pid','id');
    }

    public function parent()
    {
        return $this->belongsTo('\App\Models\Cx_Game_Cates','pid','id');
    }

    public function icon_url()
    {
        return $this->belongsTo('App\Models\Cx_Uploads','icon','image_id');
    }

    public function games()
    {
        return $this->hasMany('App\Models\Cx_Game_List','cid','id');
    }

}
