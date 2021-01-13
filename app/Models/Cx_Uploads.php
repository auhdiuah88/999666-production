<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class Cx_Uploads extends Model
{
    protected $table = "uploads";

    protected $primaryKey = "image_id";

    protected $dateFormat = "U";

    protected $guarded = [];

    public function getPathAttribute($value)
    {
        return URL::asset($value) ;
    }

    const UPDATED_AT = null;

    public function getPathUrlAttribute()
    {
        return $this->path;
    }

    protected $appends = ['path_url'];
}
