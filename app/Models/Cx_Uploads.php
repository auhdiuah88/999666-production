<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_Uploads extends Model
{
    protected $table = "uploads";

    protected $primaryKey = "image_id";

    protected $dateFormat = "U";

    protected $guarded = [];

    public function getPathAttribute($value)
    {
        return request()->getHttpHost() . '/' . $value ;
    }
}
