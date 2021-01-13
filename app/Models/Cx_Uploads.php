<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class Cx_Uploads extends Model
{
    protected $table = "uploads";

    protected $primaryKey = "images_id";

    protected $dateFormat = "U";

    protected $guarded = [];

    public $timestamps = false;

    public function getPathUrlAttribute()
    {
        return URL::asset($this->path);
    }

    protected $appends = ['path_url'];

}
