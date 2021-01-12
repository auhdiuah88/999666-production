<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cx_Banner extends Model
{
    use SoftDeletes;

    protected $table = "banner";

    protected $primaryKey = "id";

    public $timestamps = false;

}
