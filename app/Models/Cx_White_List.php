<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_White_List extends Model
{
    protected $table = "white_list";

    protected $primaryKey = "id";

    public $timestamps = false;
}
