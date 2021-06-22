<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cx_Ads extends Model
{

    protected $table = "ads";

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

    public function getContentAttribute($value)
    {
        return htmlspecialchars_decode($value);
    }

    public function getTitleAttribute($value)
    {
        return stripslashes($value);
    }

}
