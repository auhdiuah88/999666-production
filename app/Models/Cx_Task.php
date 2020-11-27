<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_Task extends Model
{
    protected $table = "tasks";

    protected $primaryKey = "id";

    public $timestamps = false;

}
