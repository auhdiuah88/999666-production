<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cx_Ag_Link extends Model
{

    protected $table = 'ag_link';

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $guarded = [];

    use SoftDeletes;

    public function getTypeAttribute($value)
    {
        return [
            'value' => $value,
            'text' => $value == 1? '代理' : '玩家'
        ];
    }

}
