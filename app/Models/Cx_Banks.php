<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_Banks extends Model
{

    protected $table = 'banks';

    protected $primaryKey = "bank_id";

    public $timestamps = false;

}
