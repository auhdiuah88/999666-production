<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cx_Uploads extends Model
{
    protected $table = "uploads";

    protected $primaryKey = "images_id";

    protected $dateFormat = "U";

    protected $guarded = [];
}
