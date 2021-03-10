<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_Sign_Product extends Model
{
    protected $table = "sign_products";

    protected $primaryKey = "id";

    public $timestamps = false;

    public function setDailyRebateAttribute($value)
    {
        $this->attributes['receive_amount'] = bcmul($value, $this->attributes['payback_cycle']);
        $this->attributes['profit'] = bcsub($this->attributes['receive_amount'], $this->attributes['amount']);
    }

}
