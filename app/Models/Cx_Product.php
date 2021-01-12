<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cx_Product extends Model
{

    use SoftDeletes;

    protected $table = "product";

    protected $primaryKey = "product_id";

    protected $dateFormat = "U";

    protected $guarded = [];

    public function banner()
    {
        return $this->belongsToMany('App\Models\Cx_Uploads','file_id','image_id')->using('App\Models\Cx_Product_Images')->withPivot('sort','product_id','file_id');
    }

}
