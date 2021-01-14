<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_Product_Orders extends Model
{

    protected $table = "product_orders";

    protected $primaryKey = "id";

    protected $dateFormat = "U";

    protected $guarded = [];

    public const UPDATED_AT = null;

    public function product()
    {
        return $this->belongsTo('App\Models\Cx_Product','product_id','product_id');
    }

}
