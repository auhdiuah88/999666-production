<?php


namespace App\Models;


use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cx_Product extends Model
{

    use SoftDeletes;

    protected $table = "product";

    protected $primaryKey = "product_id";

    protected $dateFormat = "U";

    protected $guarded = [];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    function getContentAttribute($value)
    {
        return htmlspecialchars_decode($value);
    }

    public function banner()
    {
        return $this->belongsToMany('App\Models\Cx_Uploads','product_images','product_id','file_id');
    }

    public function coverImg()
    {
        return $this->belongsTo('App\Models\Cx_Uploads','cover','image_id');
    }

}
